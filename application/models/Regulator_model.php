<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Regulator_model extends CI_Model
{
    private $table = 'as_regulator';

    public function addRegulator($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function editRegulator($old_regulator_id, $data)
    {
        $this->db->where('regulator_id', $old_regulator_id);
        return $this->db->update($this->table, $data);
    }

    public function deleteRegulator($regulator_id)
    {
        $this->db->where('regulator_id', $regulator_id);
        return $this->db->delete($this->table);
    }

    public function getRegulator($regulator_id)
    {
        $this->db->where('regulator_id', $regulator_id);
        $query = $this->db->get($this->table);
        return $query->row_array();
    }

    public function getAllRegulator()
    {
        $this->db->order_by('updated_at', 'DESC');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    public function countRegulator()
    {
        return $this->db->count_all($this->table);
    }

    public function getRegulatorFilter($limit, $offset, $filter_type = null, $search = null, $sort_by = 'updated_at', $sort_order = 'DESC')
    {
        if ($filter_type) {
            $this->db->like('type', $filter_type);
        }

        if ($search) {
            $this->db->group_start();
            $this->db->like('regulator_id', $search);
            $this->db->or_like('type', $search);
            $this->db->group_end();
        }

        $allowed_sort = ['regulator_id', 'type', 'min_stock', 'created_at', 'updated_at'];
        if (!in_array($sort_by, $allowed_sort)) {
            $sort_by = 'updated_at';
        }

        $sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';

        $this->db->order_by($sort_by, $sort_order);
        $this->db->limit($limit, $offset);

        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    /**
     * Generate regulator_id in format: reg-{type}
     * Example: reg-AR2000, reg-AR3000
     */
    public function generateRegulatorId($type)
    {
        // Convert type to lowercase and remove spaces
        $type_clean = strtolower(str_replace(' ', '', $type));
        return 'reg-' . $type_clean;
    }

    /**
     * Check if regulator with same type already exists
     */
    public function check_regulator_combination($type)
    {
        $this->db->where('type', $type);
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }
}
