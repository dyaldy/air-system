<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Fitting_subtype_model
 * Manages fitting subtypes and their hierarchical relationship with main types.
 */
class Fitting_subtype_model extends CI_Model
{
    private $table = 'as_fitting_subtypes';

    /**
     * Get all subtypes for all parent types
     *
     * @return array
     */
    public function getAllSubtypes(): array
    {
        $this->db->order_by('parent_type', 'ASC');
        $this->db->order_by('subtype', 'ASC');
        return $this->db->get($this->table)->result_array();
    }

    /**
     * Get subtypes for a specific parent type
     *
     * @param string $parentType
     * @return array
     */
    public function getSubtypesByParentType(string $parentType): array
    {
        $this->db->where('parent_type', strtoupper($parentType));
        $this->db->order_by('subtype', 'ASC');
        return $this->db->get($this->table)->result_array();
    }

    /**
     * Get a specific subtype by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        return $this->db->get_where($this->table, ['id' => $id])->row_array();
    }

    /**
     * Get a specific subtype by parent type and subtype name
     *
     * @param string $parentType
     * @param string $subtype
     * @return array|null
     */
    public function getByParentAndSubtype(string $parentType, string $subtype): ?array
    {
        return $this->db->get_where($this->table, [
            'parent_type' => strtoupper($parentType),
            'subtype' => strtoupper($subtype)
        ])->row_array();
    }

    /**
     * Add a new subtype
     *
     * @param string $parentType
     * @param string $subtype
     * @param string|null $description
     * @param string|null $imageFilename
     * @return int
     */
    public function addSubtype(string $parentType, string $subtype, ?string $description = null, ?string $imageFilename = null): int
    {
        $data = [
            'parent_type' => strtoupper($parentType),
            'subtype' => strtoupper($subtype),
            'description' => $description,
            'image' => $imageFilename,
        ];

        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Edit an existing subtype
     *
     * @param int $id
     * @param string $parentType
     * @param string $subtype
     * @param string|null $description
     * @param string|null $imageFilename
     * @return void
     */
    public function editSubtype(int $id, string $parentType, string $subtype, ?string $description = null, ?string $imageFilename = null): void
    {
        $data = [
            'parent_type' => strtoupper($parentType),
            'subtype' => strtoupper($subtype),
            'description' => $description,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($imageFilename !== null) {
            $data['image'] = $imageFilename;
        }

        $this->db->where('id', $id)->update($this->table, $data);
    }

    /**
     * Delete a subtype
     *
     * @param int $id
     * @return void
     */
    public function deleteSubtype(int $id): void
    {
        $this->db->where('id', $id)->delete($this->table);
    }

    /**
     * Check if a subtype combination already exists
     *
     * @param string $parentType
     * @param string $subtype
     * @param int|null $excludeId ID to exclude from check (for editing)
     * @return bool
     */
    public function isSubtypeExists(string $parentType, string $subtype, ?int $excludeId = null): bool
    {
        $this->db->where('parent_type', strtoupper($parentType));
        $this->db->where('subtype', strtoupper($subtype));

        if ($excludeId !== null) {
            $this->db->where('id !=', $excludeId);
        }

        return $this->db->count_all_results($this->table) > 0;
    }

    /**
     * Check if a subtype is being used in any fittings
     *
     * @param string $parentType
     * @param string $subtype
     * @return bool
     */
    public function isSubtypeInUse(string $parentType, string $subtype): bool
    {
        return $this->db->where('type', strtoupper($parentType))
            ->where('subtype', strtoupper($subtype))
            ->count_all_results('as_fitting') > 0;
    }

    /**
     * Get usage count for a specific subtype
     *
     * @param string $parentType
     * @param string $subtype
     * @return int
     */
    public function getSubtypeUsageCount(string $parentType, string $subtype): int
    {
        return $this->db->where('type', strtoupper($parentType))
            ->where('subtype', strtoupper($subtype))
            ->count_all_results('as_fitting');
    }

    /**
     * Get all subtypes grouped by parent type
     *
     * @return array
     */
    public function getSubtypesGroupedByParent(): array
    {
        $subtypes = $this->getAllSubtypes();
        $grouped = [];

        foreach ($subtypes as $subtype) {
            $parentType = $subtype['parent_type'];
            if (!isset($grouped[$parentType])) {
                $grouped[$parentType] = [];
            }
            $grouped[$parentType][] = $subtype;
        }

        return $grouped;
    }
}
