<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Project_batch_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Create a new project batch
     */
    public function create_batch($location_id, $category, $type_id, $project_name, $project_notes, $quantity, $created_by)
    {
        $location_id = strtoupper($location_id);

        // Generate unique batch ID
        $batch_id = $this->generate_batch_id($location_id, $category, $type_id);

        $batch_data = [
            'batch_id' => $batch_id,
            'location_id' => $location_id,
            'category' => $category,
            'type_id' => $type_id,
            'project_name' => $project_name,
            'project_notes' => $project_notes,
            'batch_quantity' => $quantity,
            'remaining_quantity' => $quantity,
            'created_by' => $created_by
        ];

        $result = $this->db->insert('as_project_batches', $batch_data);
        return $result ? $batch_id : false;
    }

    /**
     * Get project batches for a specific item and location
     */
    public function get_project_batches($location_id, $category, $type_id)
    {
        $location_id = strtoupper($location_id);

        $this->db->select('pb.*, u.name as created_by_name');
        $this->db->from('as_project_batches pb');
        $this->db->join('as_user u', 'pb.created_by = u.nik', 'left');
        $this->db->where('pb.location_id', $location_id);
        $this->db->where('pb.category', $category);
        $this->db->where('pb.type_id', $type_id);
        $this->db->where('pb.remaining_quantity >', 0);
        $this->db->order_by('pb.created_at', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Get all notes for project batches of a specific type
     */
    public function get_all_project_notes($category, $type_id)
    {
        $this->db->select('pb.*, u.name as created_by_name');
        $this->db->from('as_project_batches pb');
        $this->db->join('as_user u', 'pb.created_by = u.nik', 'left');
        $this->db->where('pb.category', $category);
        $this->db->where('pb.type_id', $type_id);
        $this->db->where('pb.remaining_quantity >', 0);
        $this->db->order_by('pb.created_at', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Take items from project batches (FIFO - First In, First Out)
     */
    public function take_from_batches($location_id, $category, $type_id, $quantity)
    {
        $location_id = strtoupper($location_id);

        $batches = $this->get_project_batches($location_id, $category, $type_id);
        $remaining_to_take = $quantity;
        $taken_from_batches = [];

        foreach ($batches as $batch) {
            if ($remaining_to_take <= 0) break;

            $available_in_batch = $batch['remaining_quantity'];
            $take_from_this_batch = min($remaining_to_take, $available_in_batch);

            if ($take_from_this_batch > 0) {
                // Update batch remaining quantity
                $new_remaining = $available_in_batch - $take_from_this_batch;
                $this->db->where('batch_id', $batch['batch_id']);
                $this->db->update('as_project_batches', ['remaining_quantity' => $new_remaining]);

                $taken_from_batches[] = [
                    'batch_id' => $batch['batch_id'],
                    'project_name' => $batch['project_name'],
                    'project_notes' => $batch['project_notes'],
                    'quantity_taken' => $take_from_this_batch,
                    'created_by_name' => $batch['created_by_name'],
                    'created_at' => $batch['created_at']
                ];

                $remaining_to_take -= $take_from_this_batch;
            }
        }

        return [
            'success' => $remaining_to_take == 0,
            'taken_batches' => $taken_from_batches,
            'remaining_needed' => $remaining_to_take
        ];
    }

    /**
     * Generate unique batch ID
     */
    private function generate_batch_id($location_id, $category, $type_id)
    {
        $prefix = 'PB'; // Project Batch
        $date = date('Ymd');
        $time = date('His');
        $microseconds = substr(microtime(), 2, 6); // Get microseconds for uniqueness

        // Create base batch ID
        $base_id = $prefix . '-' . $location_id . '-' . $category . '-' . $date . '-' . $time . '-' . $microseconds;

        // Ensure uniqueness by checking database and adding counter if needed
        $counter = 0;
        $batch_id = $base_id;

        while ($this->batch_id_exists($batch_id)) {
            $counter++;
            $batch_id = $base_id . '-' . $counter;
        }

        return $batch_id;
    }

    /**
     * Check if batch ID already exists
     */
    private function batch_id_exists($batch_id)
    {
        $this->db->where('batch_id', $batch_id);
        $query = $this->db->get('as_project_batches');
        return $query->num_rows() > 0;
    }

    /**
     * Take items from a specific batch
     */
    public function take_from_specific_batch($batch_id, $quantity)
    {
        $batch = $this->get_batch_by_id($batch_id);

        if (!$batch) {
            return ['success' => false, 'message' => 'Batch not found'];
        }

        if ($batch->remaining_quantity < $quantity) {
            return [
                'success' => false,
                'message' => 'Insufficient quantity in batch. Available: ' . $batch->remaining_quantity
            ];
        }

        $new_remaining = $batch->remaining_quantity - $quantity;

        $this->db->where('batch_id', $batch_id);
        $result = $this->db->update('as_project_batches', ['remaining_quantity' => $new_remaining]);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Items taken from batch successfully',
                'batch_info' => [
                    'batch_id' => $batch_id,
                    'project_name' => $batch->project_name,
                    'project_notes' => $batch->project_notes,
                    'quantity_taken' => $quantity,
                    'remaining_quantity' => $new_remaining
                ]
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to update batch'];
        }
    }

    /**
     * Add back quantity to batch (for rollback scenarios)
     */
    public function add_back_to_batch($batch_id, $quantity)
    {
        $this->db->set('remaining_quantity', 'remaining_quantity + ' . (int)$quantity, FALSE);
        $this->db->where('batch_id', $batch_id);
        return $this->db->update('as_project_batches');
    }

    /**
     * Get batch details by ID
     */
    public function get_batch_by_id($batch_id)
    {
        $this->db->select('pb.*, u.name as created_by_name');
        $this->db->from('as_project_batches pb');
        $this->db->join('as_user u', 'pb.created_by = u.nik', 'left');
        $this->db->where('pb.batch_id', $batch_id);

        return $this->db->get()->row();
    }

    /**
     * Get all batches for a specific item (for management interface)
     */
    public function get_batches_by_item($category, $type_id)
    {
        $this->db->select('pb.*, u.name as created_by_name');
        $this->db->from('as_project_batches pb');
        $this->db->join('as_user u', 'pb.created_by = u.nik', 'left');
        $this->db->where('pb.category', $category);
        $this->db->where('pb.type_id', $type_id);
        $this->db->order_by('pb.created_at', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Create batch without location (for management interface)
     */
    public function create_item_batch($category, $type_id, $project_name, $project_notes, $quantity)
    {
        // Generate unique batch ID
        $batch_id = $this->generate_batch_id_simple($category, $type_id);

        $batch_data = [
            'batch_id' => $batch_id,
            'location_id' => 'GENERAL', // Default location for management batches
            'category' => $category,
            'type_id' => $type_id,
            'project_name' => $project_name,
            'project_notes' => $project_notes,
            'batch_quantity' => $quantity,
            'remaining_quantity' => $quantity,
            'created_by' => 0 // System user
        ];

        $result = $this->db->insert('as_project_batches', $batch_data);
        return $result ? $batch_id : false;
    }

    /**
     * Update batch information (only editable fields)
     */
    public function update_batch_info($batch_id, $project_name, $project_notes)
    {
        $update_data = [
            'project_name' => $project_name,
            'project_notes' => $project_notes
        ];

        $this->db->where('batch_id', $batch_id);
        return $this->db->update('as_project_batches', $update_data);
    }

    /**
     * Update batch information
     */
    public function update_batch($batch_id, $project_name, $project_notes, $initial_quantity, $remaining_quantity)
    {
        $update_data = [
            'project_name' => $project_name,
            'project_notes' => $project_notes,
            'batch_quantity' => $initial_quantity,
            'remaining_quantity' => $remaining_quantity,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('batch_id', $batch_id);
        return $this->db->update('as_project_batches', $update_data);
    }

    /**
     * Delete a batch
     */
    public function delete_batch($batch_id)
    {
        $this->db->where('batch_id', $batch_id);
        return $this->db->delete('as_project_batches');
    }

    /**
     * Generate simple batch ID (for management interface)
     */
    private function generate_batch_id_simple($category, $type_id)
    {
        $prefix = strtoupper(substr($category, 0, 3)) . '_' . $type_id;
        $timestamp = date('YmdHis');
        $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

        return $prefix . '_' . $timestamp . '_' . $random;
    }

    /**
     * Get all batches for a specific location
     */
    public function get_batches_by_location($location_id)
    {
        $this->db->select('pb.*, u.name as created_by_name');
        $this->db->from('as_project_batches pb');
        $this->db->join('as_user u', 'pb.created_by = u.nik', 'left');
        $this->db->where('pb.location_id', $location_id);
        $this->db->where('pb.remaining_quantity >', 0);
        $this->db->order_by('pb.created_at', 'DESC');

        return $this->db->get()->result_array();
    }
}
