<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fitting_type extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['file', 'form']);

        // Check user authentication using common helper
        check_user_authentication();

        $this->load->model('Fitting_type_model');
        $this->load->model('Fitting_subtype_model');
        $this->load->library('form_validation');
    }

    public function index(): void
    {
        $types = $this->Fitting_type_model->getAllTypes();

        // Add usage information and subtype count to each type
        foreach ($types as &$type) {
            $type['usage_count'] = $this->Fitting_type_model->getUsageCount($type['type']);
            $type['is_in_use'] = $type['usage_count'] > 0;

            // Get subtype count for this type
            $subtypes = $this->Fitting_subtype_model->getSubtypesByParentType($type['type']);
            $type['subtype_count'] = count($subtypes);
            $type['has_subtypes'] = $type['subtype_count'] > 0;
        }

        $data['title'] = 'Kelola Type Fitting';
        $data['types'] = $types;
        render_view('fitting/manage_types', $data);
    }

    public function add(): void
    {
        if ($this->input->method() === 'post') {
            $type = $this->input->post('type', true);
            $this->form_validation->set_rules('type', 'Type', 'required|trim|max_length[30]|callback_validate_type_format');
            if ($this->form_validation->run() && !$this->Fitting_type_model->isTypeExists($type)) {
                $imageFilename = $this->handleUpload();
                $this->Fitting_type_model->addType($type, $imageFilename);
                set_message(['success', 'Type berhasil ditambahkan']);
                redirect('fitting_type');
            } else {
                set_message(['danger', 'Type sudah ada atau data tidak valid']);
            }
        }
        $data = [
            'title' => 'Tambah Type Fitting',
            'form_action' => site_url('fitting_type/add'),
            'type' => [],
        ];
        render_view('fitting/type_form', $data);
    }

    public function edit(int $id): void
    {
        $typeRow = $this->Fitting_type_model->getById($id);
        if (!$typeRow) {
            set_message(['danger', 'Type tidak ditemukan']);
            redirect('fitting_type');
        }

        if ($this->input->method() === 'post') {
            $type = $this->input->post('type', true);
            $this->form_validation->set_rules('type', 'Type', 'required|trim|max_length[30]|callback_validate_type_format');

            if ($this->form_validation->run()) {
                $imageFilename = $this->handleUpload();
                $this->Fitting_type_model->editType($id, $type, $imageFilename);
                set_message(['success', 'Type berhasil diperbarui']);
                redirect('fitting_type');
            }
        }

        // Get subtypes for this type
        $subtypes = $this->Fitting_subtype_model->getSubtypesByParentType($typeRow['type']);

        $data = [
            'title' => 'Edit Type Fitting',
            'form_action' => site_url('fitting_type/edit/' . $id),
            'type' => $typeRow,
            'subtypes' => $subtypes,
        ];
        render_view('fitting/type_form', $data);
    }

    public function delete(int $id): void
    {
        $typeRow = $this->Fitting_type_model->getById($id);
        if (!$typeRow) {
            set_message(['danger', 'Type tidak ditemukan']);
            redirect('fitting_type');
        }

        // Check if type is being used by any fitting records
        if ($this->Fitting_type_model->isTypeInUse($typeRow['type'])) {
            $usageCount = $this->Fitting_type_model->getUsageCount($typeRow['type']);
            set_message(['warning', "Type '{$typeRow['type']}' tidak dapat dihapus karena sedang digunakan oleh {$usageCount} fitting. Hapus terlebih dahulu fitting yang menggunakan type ini."]);
            redirect('fitting_type');
        }

        // delete image file if exists
        if (!empty($typeRow['image'])) {
            $imagePath = FCPATH . 'assets/img/fitting_types/' . $typeRow['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $this->Fitting_type_model->deleteType($id);
        set_message(['success', 'Type berhasil dihapus']);
        redirect('fitting_type');
    }

    /**
     * Get subtypes for a specific type (AJAX endpoint)
     */
    public function getSubtypes(): void
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $type = $this->input->post('type', true);
        if (empty($type)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([]));
            return;
        }

        $subtypes = $this->Fitting_subtype_model->getSubtypesByParentType($type);

        // Add usage information for each subtype
        foreach ($subtypes as &$subtype) {
            $subtype['is_in_use'] = $this->Fitting_subtype_model->isSubtypeInUse($type, $subtype['subtype']);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($subtypes));
    }

    /**
     * Add a new subtype for a specific type
     */
    public function addSubtype(): void
    {
        // if (!$this->input->is_ajax_request()) {
        //     show_404();
        // }

        $parentType = $this->input->post('parent_type', true);
        $subtype = $this->input->post('subtype', true);
        $description = $this->input->post('description', true);

        if (empty($parentType) || empty($subtype)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Parent type dan subtype harus diisi']));
            return;
        }

        // Check if parent type exists
        if (!$this->Fitting_type_model->isTypeExists($parentType)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Type fitting tidak ditemukan']));
            return;
        }

        // Validate subtype format
        if (!preg_match('/^[A-Z0-9_]+$/', $subtype)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Subtype harus menggunakan huruf besar, angka, dan underscore saja (contoh: MALE_THREAD, FEMALE_THREAD)']));
            return;
        }

        // Check if subtype already exists for this parent type
        if ($this->Fitting_subtype_model->isSubtypeExists($parentType, $subtype)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Subtype sudah ada untuk type ini']));
            return;
        }

        try {
            $this->Fitting_subtype_model->addSubtype($parentType, $subtype, $description);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Subtype berhasil ditambahkan']));
        } catch (Exception $e) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Gagal menambahkan subtype: ' . $e->getMessage()]));
        }
    }

    /**
     * Update a subtype
     */
    public function updateSubtype(): void
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id', true);
        $parentType = $this->input->post('parent_type', true);
        $subtype = $this->input->post('subtype', true);
        $description = $this->input->post('description', true);

        if (empty($id) || empty($parentType) || empty($subtype)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'ID, parent type dan subtype harus diisi']));
            return;
        }

        // Validate subtype format
        if (!preg_match('/^[A-Z0-9_]+$/', $subtype)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Subtype harus menggunakan huruf besar, angka, dan underscore saja (contoh: MALE_THREAD, FEMALE_THREAD)']));
            return;
        }

        // Check if subtype already exists for this parent type (excluding current subtype)
        if ($this->Fitting_subtype_model->isSubtypeExists($parentType, $subtype, $id)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Subtype sudah ada untuk type ini']));
            return;
        }

        try {
            $this->Fitting_subtype_model->editSubtype($id, $parentType, $subtype, $description);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Subtype berhasil diperbarui']));
        } catch (Exception $e) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Gagal memperbarui subtype: ' . $e->getMessage()]));
        }
    }

    /**
     * Delete a subtype
     */
    public function deleteSubtype(): void
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id', true);

        if (empty($id)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'ID subtype harus diisi']));
            return;
        }

        // Get subtype data to check usage
        $subtypeData = $this->Fitting_subtype_model->getById($id);
        if (!$subtypeData) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Subtype tidak ditemukan']));
            return;
        }

        // Check if subtype is being used by any fitting
        if ($this->Fitting_subtype_model->isSubtypeInUse($subtypeData['parent_type'], $subtypeData['subtype'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Subtype tidak dapat dihapus karena sedang digunakan oleh fitting']));
            return;
        }

        try {
            $this->Fitting_subtype_model->deleteSubtype($id);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Subtype berhasil dihapus']));
        } catch (Exception $e) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Gagal menghapus subtype: ' . $e->getMessage()]));
        }
    }

    private function handleUpload(): ?string
    {
        if (empty($_FILES['image']['name'])) {
            return null; // No file uploaded, return null (will not update image field)
        }

        $config['upload_path'] = FCPATH . 'assets/img/fitting_types/';
        if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0755, true);
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $config['max_size'] = 2048; // 2MB
        $fileName = time() . '-' . preg_replace('/[^a-z0-9\-\.]/i', '-', $_FILES['image']['name']);
        $config['file_name'] = $fileName;

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('image')) {
            $data = $this->upload->data();

            // Process image to remove background
            $processedPath = $this->processImageBackground($data['full_path']);

            // Return the processed filename (might be different if converted to PNG)
            return basename($processedPath ?: $data['full_path']);
        } else {
            set_message(['danger', 'Upload gambar gagal: ' . $this->upload->display_errors()]);
            return null;
        }
    }

    /**
     * Custom validation for type format (uppercase letters, numbers, and underscores only)
     */
    public function validate_type_format($str): bool
    {
        if (!preg_match('/^[A-Z0-9_]+$/', $str)) {
            $this->form_validation->set_message('validate_type_format', 'Type harus menggunakan huruf besar, angka, dan underscore saja (contoh: ELBOW_90, TEE, REDUCER)');
            return false;
        }
        return true;
    }

    /**
     * Process uploaded image to remove white/light backgrounds
     * Skip processing if image already has transparency
     */
    private function processImageBackground(string $imagePath): ?string
    {
        if (!extension_loaded('gd')) {
            return $imagePath; // GD extension not available, return original
        }

        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) return $imagePath;

        // Create image resource based on type
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($imagePath);
                // Check if PNG already has transparency
                if ($this->hasTransparency($image)) {
                    imagedestroy($image);
                    return $imagePath; // Image already has transparency, return original
                }
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($imagePath);
                break;
            default:
                return $imagePath; // Unsupported format, return original
        }

        if (!$image) return $imagePath;

        // Check if image needs background removal
        if (!$this->needsBackgroundRemoval($image)) {
            imagedestroy($image);
            return $imagePath; // Image doesn't need processing, return original
        }

        // Get image dimensions
        $width = imagesx($image);
        $height = imagesy($image);

        // Create a new true color image with transparency
        $newImage = imagecreatetruecolor($width, $height);

        // Enable alpha blending and save alpha channel
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);

        // Fill with transparent background
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $transparent);

        // Process each pixel
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($image, $x, $y);
                $colors = imagecolorsforindex($image, $rgb);

                // Calculate brightness (0-255)
                $brightness = ($colors['red'] + $colors['green'] + $colors['blue']) / 3;

                // Remove white/very light backgrounds (adjust threshold as needed)
                if ($brightness > 240) {
                    // Make this pixel transparent
                    $newColor = imagecolorallocatealpha(
                        $newImage,
                        $colors['red'],
                        $colors['green'],
                        $colors['blue'],
                        127
                    );
                } else {
                    // Keep the original pixel
                    $newColor = imagecolorallocate(
                        $newImage,
                        $colors['red'],
                        $colors['green'],
                        $colors['blue']
                    );
                }

                imagesetpixel($newImage, $x, $y, $newColor);
            }
        }

        // Save the processed image as PNG (to preserve transparency)
        $pngPath = preg_replace('/\.[^.]+$/', '.png', $imagePath);
        imagepng($newImage, $pngPath, 9); // Max compression

        // Clean up memory
        imagedestroy($image);
        imagedestroy($newImage);

        // Remove original if different from PNG
        if ($pngPath !== $imagePath) {
            unlink($imagePath);
        }

        return $pngPath;
    }

    /**
     * Check if PNG image already has transparency
     */
    private function hasTransparency($image): bool
    {
        $width = imagesx($image);
        $height = imagesy($image);

        // Sample a few pixels to check for transparency
        $samplePoints = [
            [0, 0],
            [$width - 1, 0],
            [0, $height - 1],
            [$width - 1, $height - 1], // corners
            [$width / 2, $height / 2] // center
        ];

        foreach ($samplePoints as [$x, $y]) {
            $rgb = imagecolorat($image, (int)$x, (int)$y);
            $colors = imagecolorsforindex($image, $rgb);

            // If any sample point has alpha channel transparency
            if (isset($colors['alpha']) && $colors['alpha'] > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if image needs background removal by analyzing edge pixels
     */
    private function needsBackgroundRemoval($image): bool
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $lightPixelCount = 0;
        $totalSamples = 0;

        // Sample edge pixels (where backgrounds usually are)
        $edgePixels = [
            // Top edge
            ...array_map(fn($x) => [$x, 0], range(0, $width - 1, max(1, $width / 20))),
            // Bottom edge
            ...array_map(fn($x) => [$x, $height - 1], range(0, $width - 1, max(1, $width / 20))),
            // Left edge
            ...array_map(fn($y) => [0, $y], range(0, $height - 1, max(1, $height / 20))),
            // Right edge
            ...array_map(fn($y) => [$width - 1, $y], range(0, $height - 1, max(1, $height / 20)))
        ];

        foreach ($edgePixels as [$x, $y]) {
            $rgb = imagecolorat($image, (int)$x, (int)$y);
            $colors = imagecolorsforindex($image, $rgb);

            $brightness = ($colors['red'] + $colors['green'] + $colors['blue']) / 3;

            if ($brightness > 240) {
                $lightPixelCount++;
            }
            $totalSamples++;
        }

        // If more than 60% of edge pixels are light, assume it needs background removal
        return $totalSamples > 0 && ($lightPixelCount / $totalSamples) > 0.6;
    }
}
