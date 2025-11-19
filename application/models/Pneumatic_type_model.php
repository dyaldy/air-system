<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Pneumatic_type_model
 * Manages pneumatic types and their images.
 */
class Pneumatic_type_model extends CI_Model
{
    private $table = 'as_pneumatic_types';

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
            'created_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
        ];
        $this->db->insert($this->table, $data);
        return (int)$this->db->insert_id();
    }

    public function editType(int $id, string $type, ?string $imageFilename = null): bool
    {
        $data = [
            'type' => strtoupper($type),
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
        ];
        if ($imageFilename !== null) {
            $data['image'] = $imageFilename;
        }
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    public function deleteType(int $id): bool
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function isTypeExists(string $type, ?int $excludeId = null): bool
    {
        $this->db->where('type', strtoupper($type));
        if ($excludeId !== null) {
            $this->db->where('id !=', $excludeId);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    /**
     * Check if a pneumatic type is being used by any pneumatic records.
     *
     * @param string $type The type to check
     * @return bool True if type is being used, false otherwise
     */
    public function isTypeInUse(string $type): bool
    {
        $this->db->where('type', strtoupper($type));
        return $this->db->count_all_results('as_pneumatic') > 0;
    }

    /**
     * Get count of pneumatic records using this type.
     *
     * @param string $type The type to check
     * @return int Number of pneumatic records using this type
     */
    public function getUsageCount(string $type): int
    {
        $this->db->where('type', strtoupper($type));
        return $this->db->count_all_results('as_pneumatic');
    }
}
