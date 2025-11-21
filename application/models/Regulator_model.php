<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Regulator_model extends CI_Model
{
    private $table = 'as_regulator';

    /**
     * Get regulators with filtering, sorting and pagination (ASRS pattern)
     */
    public function getRegulator($limit, $offset, $search = null, $filter = null, $sort = null)
    {
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('regulator_id', $search);
            $this->db->or_like('type', $search);
            $this->db->group_end();
        }

        if (!empty($filter)) {
            $filterParts = explode('-', $filter, 2);
            if (count($filterParts) == 2) {
                $column = $filterParts[0];
                $value  = $filterParts[1];
                if ($column === 'type') {
                    $this->db->like('type', $value);
                }
            }
        }

        if (!empty($sort)) {
            $sortParts = explode('-', $sort, 2);
            if (count($sortParts) == 2) {
                $column = $sortParts[0];
                $order  = strtoupper($sortParts[1]);
                $allowed = ['regulator_id', 'type', 'min_stock', 'created_at', 'updated_at'];
                if (in_array($column, $allowed) && in_array($order, ['ASC', 'DESC'])) {
                    $this->db->order_by($column, $order);
                }
            }
        } else {
            $this->db->order_by('updated_at', 'DESC');
        }

        $this->db->limit($limit, $offset);
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    /**
     * Get filter options for dropdown (ASRS pattern)
     */
    public function getRegulatorFilter($column, $search = null, $filter = null)
    {
        $this->db->select($column);
        $this->db->distinct();

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('regulator_id', $search);
            $this->db->or_like('type', $search);
            $this->db->group_end();
        }

        if (!empty($filter)) {
            $filterParts = explode('-', $filter, 2);
            if (count($filterParts) == 2) {
                $filterColumn = $filterParts[0];
                $filterValue  = $filterParts[1];
                if ($filterColumn === 'type' && $column !== 'type') {
                    $this->db->like('type', $filterValue);
                }
            }
        }

        $this->db->order_by($column, 'ASC');
        $query = $this->db->get($this->table);
        return array_column($query->result_array(), $column);
    }

    /**
     * Count regulators with filters (ASRS pattern)
     */
    public function countRegulator($search = null, $filter = null)
    {
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('regulator_id', $search);
            $this->db->or_like('type', $search);
            $this->db->group_end();
        }

        if (!empty($filter)) {
            $filterParts = explode('-', $filter, 2);
            if (count($filterParts) == 2) {
                $column = $filterParts[0];
                $value  = $filterParts[1];
                if ($column === 'type') {
                    $this->db->like('type', $value);
                }
            }
        }

        return $this->db->count_all_results($this->table);
    }

    /**
     * Get single regulator by ID
     */
    public function getById($regulatorId)
    {
        $this->db->where('regulator_id', $regulatorId);
        $query = $this->db->get($this->table);
        return $query->row_array();
    }

    /**
     * Get all regulators for export
     */
    public function getAllRegulator()
    {
        $this->db->order_by('updated_at', 'DESC');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    /**
     * Add new regulator
     */
    public function addRegulator()
    {
        $type = $this->input->post('type');
        $regulatorId = 'reg-' . strtolower(str_replace(' ', '', $type));

        $data = [
            'regulator_id' => $regulatorId,
            'type'         => $type,
            'min_stock'    => $this->input->post('min_stock') ?: 5,
            'created_at'   => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'updated_at'   => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
        ];

        return $this->db->insert($this->table, $data);
    }

    /**
     * Edit existing regulator
     */
    public function editRegulator($regulatorId)
    {
        $data = [
            'min_stock'  => $this->input->post('min_stock') ?: 5,
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
        ];

        $this->db->where('regulator_id', $regulatorId);
        return $this->db->update($this->table, $data);
    }

    /**
     * Delete regulator
     */
    public function deleteRegulator($regulatorId)
    {
        $this->db->where('regulator_id', $regulatorId);
        return $this->db->delete($this->table);
    }

    /**
     * Batch insert for CSV upload
     */
    public function insertBatch($data)
    {
        return $this->db->insert_batch($this->table, $data);
    }

    /**
     * Check if regulator ID already exists
     */
    public function isRegulatorIdExists($regulatorId)
    {
        $this->db->where('regulator_id', $regulatorId);
        return $this->db->count_all_results($this->table) > 0;
    }

    /**
     * Generate regulator_id in format: reg-{type}
     */
    public function generateRegulatorId($type)
    {
        return 'reg-' . strtolower(str_replace(' ', '', $type));
    }

    /**
     * Check if regulator with same type already exists (for validation)
     */
    public function check_regulator_combination($type)
    {
        $this->db->where('type', $type);
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }
}
