<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Fitting_type_model
 * Manages fitting types and their images.
 */
class Fitting_type_model extends CI_Model
{
    private string $table = 'as_fitting_types';

    public function getAllTypes(): array
    {
        $this->db->order_by('type', 'ASC');
        return $this->db->get($this->table)->result_array();
    }

    public function getById(int $id): ?array
    {
        return $this->db->get_where($this->table, ['id' => $id])->row_array();
    }

    public function getByType(string $type): ?array
    {
        return $this->db->get_where($this->table, ['type' => strtoupper($type)])->row_array();
    }

    public function addType(string $type, ?string $imageFilename = null): int
    {
        $data = [
            'type' => strtoupper($type),
            'image' => $imageFilename,
        ];
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function editType(int $id, string $type, ?string $imageFilename = null): void
    {
        $data = [
            'type' => strtoupper($type),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($imageFilename !== null) {
            $data['image'] = $imageFilename;
        }

        $this->db->where('id', $id)->update($this->table, $data);
    }

    public function deleteType(int $id): void
    {
        $this->db->where('id', $id)->delete($this->table);
    }

    public function isTypeExists(string $type): bool
    {
        return $this->db->where('type', strtoupper($type))->count_all_results($this->table) > 0;
    }

    public function isTypeInUse(string $type): bool
    {
        return $this->db->where('type', strtoupper($type))->count_all_results('as_fitting') > 0;
    }

    public function getUsageCount(string $type): int
    {
        return $this->db->where('type', strtoupper($type))->count_all_results('as_fitting');
    }
}
