<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Hose_model extends CI_Model
{
    private $table = 'as_hose';

    /**
     * Get hoses with filtering, sorting and pagination (ASRS pattern)
     */
    public function getHose($limit, $offset, $search = null, $filter = null, $sort = null)
    {
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('hose_id', $search);
            $this->db->or_like('diameter', $search);
            $this->db->group_end();
        }

        if (!empty($filter)) {
            $filterParts = explode('-', $filter, 2);
            if (count($filterParts) == 2) {
                $column = $filterParts[0];
                $value  = $filterParts[1];
                if ($column === 'diameter') {
                    $this->db->like('diameter', $value);
                }
            }
        }

        if (!empty($sort)) {
            $sortParts = explode('-', $sort, 2);
            if (count($sortParts) == 2) {
                $column = $sortParts[0];
                $order  = strtoupper($sortParts[1]);
                $allowed = ['hose_id', 'diameter', 'min_stock', 'created_at', 'updated_at'];
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
    public function getHoseFilter($column, $search = null, $filter = null)
    {
        $this->db->select($column);
        $this->db->distinct();

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('hose_id', $search);
            $this->db->or_like('diameter', $search);
            $this->db->group_end();
        }

        if (!empty($filter)) {
            $filterParts = explode('-', $filter, 2);
            if (count($filterParts) == 2) {
                $filterColumn = $filterParts[0];
                $filterValue  = $filterParts[1];
                if ($filterColumn === 'diameter' && $column !== 'diameter') {
                    $this->db->like('diameter', $filterValue);
                }
            }
        }

        $this->db->order_by($column, 'ASC');
        $query = $this->db->get($this->table);
        return array_column($query->result_array(), $column);
    }

    /**
     * Count hoses with filters (ASRS pattern)
     */
    public function countHose($search = null, $filter = null)
    {
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('hose_id', $search);
            $this->db->or_like('diameter', $search);
            $this->db->group_end();
        }

        if (!empty($filter)) {
            $filterParts = explode('-', $filter, 2);
            if (count($filterParts) == 2) {
                $column = $filterParts[0];
                $value  = $filterParts[1];
                if ($column === 'diameter') {
                    $this->db->like('diameter', $value);
                }
            }
        }

        return $this->db->count_all_results($this->table);
    }

    /**
     * Get single hose by ID
     */
    public function getById($hoseId)
    {
        $this->db->where('hose_id', $hoseId);
        $query = $this->db->get($this->table);
        return $query->row_array();
    }

    /**
     * Get all hoses for export
     */
    public function getAllHose()
    {
        $this->db->order_by('updated_at', 'DESC');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    /**
     * Add new hose
     */
    public function addHose()
    {
        $diameter = $this->input->post('diameter');
        $hoseId = 'hose-' . strtolower(str_replace(' ', '', $diameter));

        $minStock = $this->input->post('min_stock');
        $data = [
            'hose_id'    => $hoseId,
            'diameter'   => $diameter,
            'min_stock'  => ($minStock !== '' && $minStock !== null) ? (int)$minStock : null,
            'created_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'editor'     => $this->session->userdata('username'),
        ];

        return $this->db->insert($this->table, $data);
    }

    /**
     * Edit existing hose
     */
    public function editHose($hoseId)
    {
        $minStock = $this->input->post('min_stock');
        $data = [
            'min_stock'  => ($minStock !== '' && $minStock !== null) ? (int)$minStock : null,
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'editor'     => $this->session->userdata('username'),
        ];

        $this->db->where('hose_id', $hoseId);
        return $this->db->update($this->table, $data);
    }

    /**
     * Delete hose
     */
    public function deleteHose($hoseId)
    {
        $this->db->where('hose_id', $hoseId);
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
     * Check if hose ID already exists
     */
    public function isHoseIdExists($hoseId)
    {
        $this->db->where('hose_id', $hoseId);
        return $this->db->count_all_results($this->table) > 0;
    }

    /**
     * Generate hose_id in format: hose-{diameter}
     */
    public function generateHoseId($diameter)
    {
        return 'hose-' . strtolower(str_replace(' ', '', $diameter));
    }

    /**
     * Check if hose with same diameter already exists (for validation)
     */
    public function check_hose_combination($diameter)
    {
        $this->db->where('diameter', $diameter);
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }
}
