<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Solenoid_model for air-system.
 *
 * This model handles all database operations for the `as_solenoid` table,
 * including solenoid management (CRUD) and data retrieval with search,
 * pagination, and filtering capabilities.
 *
 * @package AirSystem
 * @subpackage Models
 * @category Solenoid
 * @author Apparel One Indonesia
 * @version 1.0.0
 * @property CI_DB_query_builder $db
 * @property CI_Input $input
 * @property CI_Session $session
 */
class Solenoid_model extends CI_Model
{
    /**
     * The name of the solenoid database table used by this model.
     *
     * @var string
     */
    private $solenoidTable = 'as_solenoid';

    /**
     * Retrieves a list of solenoids based on search, filter, and sort criteria.
     *
     * @param int         $limit         Number of records to retrieve.
     * @param int         $start         Offset for pagination.
     * @param string|null $searchKeyword Optional keyword to search in solenoid fields.
     * @param array|null  $filterKeyword Optional associative array of filters.
     * @param string|null $sortKeyword   Optional sort criteria in the format "field-order".
     *
     * @return array An array of solenoid records matching the criteria.
     */
    public function getSolenoid(int $limit, int $start, ?string $searchKeyword = null, ?array $filterKeyword = null, ?string $sortKeyword = null): array
    {
        $this->solenoidSearchAndFilters($searchKeyword, $filterKeyword);

        if ($sortKeyword && strpos($sortKeyword, '-') !== false) {
            [$field, $order] = explode('-', $sortKeyword, 2);
            $this->db->order_by($field, $order);
        } else {
            $this->db->order_by('updated_at', 'DESC');
        }

        return $this->db->get($this->solenoidTable, $limit, $start)->result_array();
    }

    /**
     * Counts the number of solenoid records matching search and filter criteria.
     *
     * @param string|null $searchKeyword Optional keyword to search in solenoid fields.
     * @param array|null  $filterKeyword Optional associative array of filters.
     *
     * @return int The total number of records matching the criteria.
     */
    public function countSolenoid(?string $searchKeyword = null, ?array $filterKeyword = null): int
    {
        $this->solenoidSearchAndFilters($searchKeyword, $filterKeyword);
        return $this->db->count_all_results($this->solenoidTable);
    }

    /**
     * Retrieves distinct values of a specific field for filter dropdowns.
     *
     * @param string      $field         The field to retrieve distinct values from.
     * @param string|null $searchKeyword Optional keyword to search in solenoid fields.
     * @param array|null  $filterKeyword Optional associative array of filters.
     *
     * @return array An array of distinct values.
     */
    public function getSolenoidFilter(string $field, ?string $searchKeyword = null, ?array $filterKeyword = null): array
    {
        $this->db->select($field);
        $this->solenoidSearchAndFilters($searchKeyword, $filterKeyword);
        $this->db->distinct()->order_by($field, 'ASC');
        $query = $this->db->get($this->solenoidTable);
        return array_column($query->result_array(), $field);
    }

    /**
     * Inserts a new solenoid record into the database.
     *
     * @return void
     */
    public function addSolenoid(): void
    {
        $type = strtoupper($this->input->post('type', true));
        $subtype = $this->input->post('subtype', true);
        $minStock = $this->input->post('min_stock') ? (int)$this->input->post('min_stock', true) : null;

        // Generate solenoid_id with format: sol-{type}-{subtype}
        $solenoidId = $this->generateSolenoidId($type, $subtype);

        $solenoidData = [
            'solenoid_id' => $solenoidId,
            'type'       => $type,
            'subtype'    => $subtype,
            'min_stock'  => $minStock,
            'created_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'editor'     => $this->session->userdata('user_data')['nik'],
        ];
        $this->db->insert($this->solenoidTable, $solenoidData);
    }

    /**
     * Retrieves a single solenoid record by its unique ID.
     *
     * @param string $solenoidId The ID of the solenoid to retrieve.
     *
     * @return array|null The solenoid record, or null if not found.
     */
    public function getById(string $solenoidId): ?array
    {
        return $this->db->get_where($this->solenoidTable, ['solenoid_id' => $solenoidId])->row_array();
    }

    /**
     * Updates an existing solenoid's details.
     *
     * @param string $solenoidId The ID of the solenoid to update.
     *
     * @return void
     */
    public function editSolenoid(string $solenoidId): void
    {
        $type = strtoupper($this->input->post('type', true));
        $subtype = $this->input->post('subtype', true);
        $minStock = $this->input->post('min_stock') ? (int)$this->input->post('min_stock', true) : null;

        // Generate new solenoid_id with format: sol-{type}-{subtype}
        $newSolenoidId = $this->generateSolenoidId($type, $subtype);

        $solenoidData = [
            'solenoid_id' => $newSolenoidId,
            'type'       => $type,
            'subtype'    => $subtype,
            'min_stock'  => $minStock,
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'editor'     => $this->session->userdata('user_data')['nik'],
        ];
        $this->db->update($this->solenoidTable, $solenoidData, ['solenoid_id' => $solenoidId]);
    }

    /**
     * Deletes a solenoid record based on its ID.
     *
     * @param string $solenoidId The ID of the solenoid to delete.
     *
     * @return void
     */
    public function deleteSolenoid(string $solenoidId): void
    {
        $this->db->where('solenoid_id', $solenoidId)->delete($this->solenoidTable);
    }

    /**
     * Checks if a solenoid with the same type and subtype combination exists.
     *
     * @param string $type The type to check
     * @param string $subtype The subtype to check
     * @param string|null $excludeId Optional ID to exclude from check (for edit)
     *
     * @return bool Returns true if the combination exists, false otherwise.
     */
    public function isSolenoidExists(string $type, string $subtype, ?string $excludeId = null): bool
    {
        $this->db->where('type', strtoupper($type));
        $this->db->where('subtype', $subtype);
        if ($excludeId !== null) {
            $this->db->where('solenoid_id !=', $excludeId);
        }
        return $this->db->count_all_results($this->solenoidTable) > 0;
    }

    /**
     * Generates a unique solenoid ID based on type and subtype.
     *
     * @param string $type The solenoid type
     * @param string $subtype The solenoid subtype
     *
     * @return string The generated solenoid ID in format: sol-{type}-{subtype}
     */
    private function generateSolenoidId(string $type, string $subtype): string
    {
        // Format: sol-type-subtype (all lowercase, spaces to hyphens)
        $formattedType = strtolower(str_replace(' ', '-', $type));
        $formattedSubtype = strtolower(str_replace(' ', '-', $subtype));
        return 'sol-' . $formattedType . '-' . $formattedSubtype;
    }

    /**
     * Inserts multiple solenoid records in a single batch operation.
     *
     * @param array $data An array of associative arrays containing solenoid data.
     *
     * @return void
     */
    public function insertBatch(array $data): void
    {
        $this->db->insert_batch($this->solenoidTable, $data);
    }

    /**
     * Retrieves all solenoid records for export purposes.
     *
     * @return array An array of all solenoid records.
     */
    public function getAllSolenoids(): array
    {
        $this->db->order_by('updated_at', 'DESC');
        return $this->db->get($this->solenoidTable)->result_array();
    }

    /**
     * A private helper method to apply search and filter conditions for solenoid retrieval.
     *
     * @param string|null $searchKeyword Optional keyword to search in solenoid fields.
     * @param array|null  $filterKeyword Optional associative array of filters.
     *
     * @return void
     */
    private function solenoidSearchAndFilters(?string $searchKeyword, ?array $filterKeyword): void
    {
        if ($searchKeyword && trim($searchKeyword) !== '') {
            $this->db->group_start()
                ->like('solenoid_id', trim($searchKeyword))
                ->or_like('s.type', trim($searchKeyword))
                ->or_like('subtype', trim($searchKeyword))
                ->group_end();
        }
        if ($filterKeyword && is_array($filterKeyword)) {
            foreach ($filterKeyword as $key => $value) {
                if (is_array($value) && !empty($value)) {
                    $this->db->where_in($key, $value);
                }
            }
        }
    }
}
