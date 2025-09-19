<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pneumatic_type extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('user_data')) {
            redirect(base_url());
        }
        $this->load->model('Pneumatic_type_model');
        $this->load->helper(['url', 'file', 'form']);
        $this->load->library('form_validation');
    }

    public function index(): void
    {
        $types = $this->Pneumatic_type_model->getAllTypes();

        // Add usage information to each type
        foreach ($types as &$type) {
            $type['usage_count'] = $this->Pneumatic_type_model->getUsageCount($type['type']);
            $type['is_in_use'] = $type['usage_count'] > 0;
        }

        $data['title'] = 'Kelola Type Pneumatic';
        $data['types'] = $types;
        render_view('pneumatic/manage_types', $data);
    }

    public function add(): void
    {
        if ($this->input->method() === 'post') {
            $type = $this->input->post('type', true);
            $this->form_validation->set_rules('type', 'Type', 'required|trim|max_length[10]');
            if ($this->form_validation->run() && !$this->Pneumatic_type_model->isTypeExists($type)) {
                $imageFilename = $this->handleUpload();
                $this->Pneumatic_type_model->addType($type, $imageFilename);
                set_message(['success', 'Type berhasil ditambahkan']);
                redirect('pneumatic_type');
            } else {
                set_message(['danger', 'Type sudah ada atau data tidak valid']);
            }
        }
        $data = [
            'title' => 'Tambah Type Pneumatic',
            'form_action' => site_url('pneumatic_type/add'),
            'type' => [],
        ];
        render_view('pneumatic/type_form', $data);
    }

    public function edit(int $id): void
    {
        $typeRow = $this->Pneumatic_type_model->getById($id);
        if (!$typeRow) show_404();

        if ($this->input->method() === 'post') {
            $type = $this->input->post('type', true);
            $this->form_validation->set_rules('type', 'Type', 'required|trim|max_length[10]');
            if ($this->form_validation->run() && !$this->Pneumatic_type_model->isTypeExists($type, $id)) {
                $imageFilename = $this->handleUpload($typeRow['image']);
                $this->Pneumatic_type_model->editType($id, $type, $imageFilename);
                set_message(['success', 'Type berhasil diperbarui']);
                redirect('pneumatic_type');
            } else {
                set_message(['danger', 'Type sudah ada atau data tidak valid']);
            }
        }

        $data['title'] = 'Edit Type Pneumatic';
        $data['form_action'] = site_url('pneumatic_type/edit/' . $id);
        $data['type'] = $typeRow;
        render_view('pneumatic/type_form', $data);
    }

    public function delete(int $id): void
    {
        $typeRow = $this->Pneumatic_type_model->getById($id);
        if (!$typeRow) {
            set_message(['danger', 'Type tidak ditemukan']);
            redirect('pneumatic_type');
        }

        // Check if type is being used by any pneumatic records
        if ($this->Pneumatic_type_model->isTypeInUse($typeRow['type'])) {
            $usageCount = $this->Pneumatic_type_model->getUsageCount($typeRow['type']);
            set_message(['warning', "Type '{$typeRow['type']}' tidak dapat dihapus karena sedang digunakan oleh {$usageCount} pneumatic. Hapus terlebih dahulu pneumatic yang menggunakan type ini."]);
            redirect('pneumatic_type');
        }

        // delete image file if exists
        if (!empty($typeRow['image'])) {
            $filePath = FCPATH . 'assets/img/pneumatic_types/' . $typeRow['image'];
            if (is_file($filePath)) @unlink($filePath);
        }

        try {
            $this->Pneumatic_type_model->deleteType($id);
            set_message(['success', 'Type berhasil dihapus']);
        } catch (Exception $e) {
            // Handle any database constraint errors as fallback
            log_message('error', 'Error deleting pneumatic type: ' . $e->getMessage());
            set_message(['danger', 'Gagal menghapus type. Type ini mungkin sedang digunakan oleh pneumatic lain.']);
        }

        redirect('pneumatic_type');
    }

    /**
     * Handle image upload with automatic background removal.
     * If $existing provided and upload fails, keep existing filename.
     * Returns filename or null.
     */
    private function handleUpload(?string $existing = null): ?string
    {
        if (empty($_FILES) || empty($_FILES['image']['name'])) {
            return $existing ?? null;
        }

        $config['upload_path'] = FCPATH . 'assets/img/pneumatic_types/';
        if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0755, true);
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $config['max_size'] = 2048; // 2MB
        $fileName = time() . '-' . preg_replace('/[^a-z0-9\-\.]/i', '-', $_FILES['image']['name']);
        $config['file_name'] = $fileName;

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('image')) {
            $data = $this->upload->data();

            // Process image to remove background
            $this->processImageBackground($data['full_path']);

            // remove previous file
            if ($existing) {
                $prev = $config['upload_path'] . $existing;
                if (is_file($prev)) @unlink($prev);
            }
            return $data['file_name'];
        }

        // on failure keep existing if provided
        return $existing ?? null;
    }

    /**
     * Process uploaded image to remove white/light backgrounds
     * Skip processing if image already has transparency
     */
    private function processImageBackground(string $imagePath): void
    {
        if (!extension_loaded('gd')) {
            return; // GD extension not available
        }

        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) return;

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
                    return; // Image already has transparency, skip processing
                }
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($imagePath);
                break;
            default:
                return; // Unsupported format
        }

        if (!$image) return;

        // Check if image needs background removal
        if (!$this->needsBackgroundRemoval($image)) {
            imagedestroy($image);
            return; // Image doesn't need processing
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
