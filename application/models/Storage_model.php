<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Storage_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Take items (decrease amount)
     */
    public function take_items($location_id, $category, $type_id, $quantity, $editor_nik)
    {
        $location_id = strtoupper($location_id);

        $current_item = $this->get_storage_item($location_id, $category, $type_id);

        if (!$current_item) {
            return array('success' => false, 'message' => 'Item not found in storage');
        }

        if ($current_item['amount'] < $quantity) {
            return array('success' => false, 'message' => 'Insufficient stock. Available: ' . $current_item['amount']);
        }

        $new_amount = $current_item['amount'] - $quantity;

        if ($this->update_storage($location_id, $category, $type_id, $new_amount, null, $editor_nik)) {
            return array('success' => true, 'message' => 'Items taken successfully');
        } else {
            return array('success' => false, 'message' => 'Failed to update storage');
        }
    }

    /**
     * Get all storage locations with their inventory
     */
    public function get_all_storage()
    {
        $this->db->select('s.*, u.name as editor_name');
        $this->db->from('as_storage s');
        $this->db->join('as_user u', 's.editor = u.nik', 'left');
        $this->db->order_by('s.location_id, s.category, s.type_id');
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Count total storage items (distinct combinations of location, category, type)
     */
    public function countStorage()
    {
        return $this->db->count_all_results('as_storage');
    }

    /**
     * Get storage by location
     */
    public function get_storage_by_location($location_id)
    {
        $location_id = strtoupper($location_id);

        $this->db->select('s.*, u.name as editor_name');
        $this->db->from('as_storage s');
        $this->db->join('as_user u', 's.editor = u.nik', 'left');
        $this->db->where('s.location_id', $location_id);
        $this->db->order_by('s.category, s.type_id');
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get specific storage item
     */
    public function get_storage_item($location_id, $category, $type_id)
    {
        $location_id = strtoupper($location_id);

        $this->db->where('location_id', $location_id);
        $this->db->where('category', $category);
        $this->db->where('type_id', $type_id);
        $query = $this->db->get('as_storage');
        return $query->row_array();
    }

    /**
     * Check if storage item exists
     */
    public function storage_exists($location_id, $category, $type_id)
    {
        $location_id = strtoupper($location_id);

        $this->db->where('location_id', $location_id);
        $this->db->where('category', $category);
        $this->db->where('type_id', $type_id);
        $query = $this->db->get('as_storage');
        return $query->num_rows() > 0;
    }

    /**
     * Add new storage entry
     */
    public function add_storage($data)
    {
        $storage_data = array(
            'location_id' => strtoupper($data['location_id']),
            'category' => $data['category'],
            'type_id' => $data['type_id'],
            'amount' => $data['amount'],
            'storage_data' => isset($data['storage_data']) ? json_encode($data['storage_data']) : null,
            'editor' => $data['editor']
        );

        return $this->db->insert('as_storage', $storage_data);
    }

    /**
     * Update storage amount and data
     */
    public function update_storage($location_id, $category, $type_id, $new_amount, $storage_data = null, $editor_nik = null)
    {
        $location_id = strtoupper($location_id);

        $update_data = array(
            'amount' => $new_amount,
            'updated_at' => date('Y-m-d H:i:s')
        );

        if ($storage_data !== null) {
            $update_data['storage_data'] = json_encode($storage_data);
        }

        if ($editor_nik !== null) {
            $update_data['editor'] = $editor_nik;
        }

        $this->db->where('location_id', $location_id);
        $this->db->where('category', $category);
        $this->db->where('type_id', $type_id);

        return $this->db->update('as_storage', $update_data);
    }

    /**
     * Store items (increase amount)
     */
    public function store_items($location_id, $category, $type_id, $quantity, $editor_nik, $storage_data = null)
    {
        $location_id = strtoupper($location_id);

        // Check if storage item exists
        if ($this->storage_exists($location_id, $category, $type_id)) {
            // Update existing storage
            $current_item = $this->get_storage_item($location_id, $category, $type_id);
            $new_amount = $current_item['amount'] + $quantity;

            // Merge storage data if provided
            $current_storage_data = json_decode($current_item['storage_data'], true) ?? array();
            if ($storage_data) {
                $current_storage_data = array_merge($current_storage_data, $storage_data);
            }

            return $this->update_storage($location_id, $category, $type_id, $new_amount, $current_storage_data, $editor_nik);
        } else {
            // Create new storage entry
            $data = array(
                'location_id' => $location_id,
                'category' => $category,
                'type_id' => $type_id,
                'amount' => $quantity,
                'storage_data' => $storage_data,
                'editor' => $editor_nik
            );
            return $this->add_storage($data);
        }
    }

    /**
     * Get available stock for a specific item
     */
    public function get_available_stock($category, $type_id)
    {
        $this->db->select('location_id, amount');
        $this->db->where('category', $category);
        $this->db->where('type_id', $type_id);
        $this->db->where('amount >', 0);
        $query = $this->db->get('as_storage');
        return $query->result_array();
    }

    /**
     * Get total stock across all locations for a specific item
     */
    public function get_total_stock($category, $type_id)
    {
        $this->db->select_sum('amount');
        $this->db->where('category', $category);
        $this->db->where('type_id', $type_id);
        $query = $this->db->get('as_storage');
        $result = $query->row_array();
        return $result['amount'] ?? 0;
    }

    /**
     * Get all unique locations
     */
    public function get_all_locations()
    {
        $this->db->distinct();
        $this->db->select('location_id');
        $this->db->order_by('location_id');
        $query = $this->db->get('as_storage');
        return $query->result_array();
    }

    /**
     * Get storage overview grouped by category and type
     */
    public function get_storage_overview($search_term = null)
    {
        $this->db->select('category, type_id, SUM(amount) as total_amount, COUNT(location_id) as location_count');
        $this->db->group_by(array('category', 'type_id'));
        $this->db->having('SUM(amount) >', 0);

        if ($search_term) {
            $this->db->group_start();
            $this->db->like('type_id', $search_term);
            $this->db->or_like('category', $search_term);
            $this->db->group_end();
        }

        $this->db->order_by('category, type_id');
        $query = $this->db->get('as_storage');
        return $query->result_array();
    }

    /**
     * Search storage items
     */
    public function search_storage($search_term, $location_id = null, $category = null)
    {
        $this->db->select('s.*, u.name as editor_name');
        $this->db->from('as_storage s');
        $this->db->join('as_user u', 's.editor = u.nik', 'left');

        if ($search_term) {
            $this->db->group_start();
            $this->db->like('s.type_id', $search_term);
            $this->db->or_like('s.category', $search_term);
            $this->db->or_like('s.location_id', $search_term);
            $this->db->group_end();
        }

        if ($location_id) {
            $this->db->where('s.location_id', $location_id);
        }

        if ($category) {
            $this->db->where('s.category', $category);
        }

        $this->db->order_by('s.location_id, s.category, s.type_id');
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get all locations where an item is stored
     */
    public function get_item_locations($category, $type_id)
    {
        $this->db->select('location_id, amount');
        $this->db->where('category', $category);
        $this->db->where('type_id', $type_id);
        $this->db->where('amount >', 0);
        $this->db->order_by('location_id');

        return $this->db->get('as_storage')->result_array();
    }

    /**
     * Update stock amount for a specific location
     */
    public function update_location_stock($location_id, $category, $type_id, $new_amount)
    {
        if ($new_amount <= 0) {
            // Remove from location if amount is 0 or negative
            return $this->remove_from_location($location_id, $category, $type_id);
        } else {
            $this->db->where('location_id', $location_id);
            $this->db->where('category', $category);
            $this->db->where('type_id', $type_id);

            $update_data = [
                'amount' => $new_amount,
                'date_update' => date('Y-m-d H:i:s'),
                'editor' => 'system'
            ];

            return $this->db->update('as_storage', $update_data);
        }
    }

    /**
     * Remove item from specific location
     */
    public function remove_from_location($location_id, $category, $type_id)
    {
        $this->db->where('location_id', $location_id);
        $this->db->where('category', $category);
        $this->db->where('type_id', $type_id);

        return $this->db->delete('as_storage');
    }
}
