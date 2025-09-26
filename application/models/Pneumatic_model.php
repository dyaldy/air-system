<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Pneumatic_model for air-system.
 *
 * This model handles all database operations for the `as_pneumatic` table,
 * including pneumatic management (CRUD) and data retrieval with search,
 * pagination, and filtering capabilities.
 *
 * @package AirSystem
 * @subpackage Models
 * @category Pneumatic
 * @author Apparel One Indonesia
 * @version 1.0.0
 * @property CI_DB_query_builder $db
 * @property CI_Input $input
 * @property CI_Session $session
 */
class Pneumatic_model extends CI_Model
{
    /**
     * The name of the pneumatic database table used by this model.
     *
     * @var string
     */
    private string $pneumaticTable = 'as_pneumatic';

    /**
     * Retrieves a list of pneumatics based on search, filter, and sort criteria.
     *
     * @param int         $limit         Number of records to retrieve.
     * @param int         $start         Offset for pagination.
     * @param string|null $searchKeyword Optional keyword to search in pneumatic fields.
     * @param array|null  $filterKeyword Optional associative array of filters.
     * @param string|null $sortKeyword   Optional sort criteria in the format "field-order".
     *
     * @return array An array of pneumatic records matching the criteria.
     */
    public function getPneumatic(int $limit, int $start, ?string $searchKeyword = null, ?array $filterKeyword = null, ?string $sortKeyword = null): array
    {
        $this->pneumaticSearchAndFilters($searchKeyword, $filterKeyword);

        if ($sortKeyword && strpos($sortKeyword, '-') !== false) {
            [$field, $order] = explode('-', $sortKeyword, 2);
            $this->db->order_by($field, $order);
        } else {
            $this->db->order_by('updated_at', 'DESC');
        }

        return $this->db->get($this->pneumaticTable, $limit, $start)->result_array();
    }

    /**
     * Counts the number of pneumatic records matching search and filter criteria.
     *
     * @param string|null $searchKeyword Optional keyword to search in pneumatic fields.
     * @param array|null  $filterKeyword Optional associative array of filters.
     *
     * @return int The total number of records matching the criteria.
     */
    public function countPneumatic(?string $searchKeyword = null, ?array $filterKeyword = null): int
    {
        $this->pneumaticSearchAndFilters($searchKeyword, $filterKeyword);
        return $this->db->count_all_results($this->pneumaticTable);
    }

    /**
     * Retrieves distinct values of a specific field for filter dropdowns.
     *
     * @param string      $field         The field to retrieve distinct values from.
     * @param string|null $searchKeyword Optional keyword to search in pneumatic fields.
     * @param array|null  $filterKeyword Optional associative array of filters.
     *
     * @return array An array of distinct values.
     */
    public function getPneumaticFilter(string $field, ?string $searchKeyword = null, ?array $filterKeyword = null): array
    {
        $this->db->select($field);
        $this->pneumaticSearchAndFilters($searchKeyword, $filterKeyword);
        $this->db->distinct()->order_by($field, 'ASC');
        $query = $this->db->get($this->pneumaticTable);
        return array_column($query->result_array(), $field);
    }

    /**
     * Inserts a new pneumatic record into the database.
     *
     * @return void
     */
    public function addPneumatic(): void
    {
        $brand = strtoupper($this->input->post('brand', true));
        $type = strtoupper($this->input->post('type', true));
        $bore = (int)$this->input->post('bore', true);
        $stroke = (int)$this->input->post('stroke', true);
        $minStock = $this->input->post('min_stock') ? (int)$this->input->post('min_stock', true) : null;

        // Generate pneumatic_id with format: pnm-{brand}-{type}-{bore}-{stroke}
        $pneumaticId = $this->generatePneumaticId($brand, $type, $bore, $stroke);

        $pneumaticData = [
            'pneumatic_id' => $pneumaticId,
            'brand'        => $brand,
            'type'         => $type,
            'bore'         => $bore,
            'stroke'       => $stroke,
            'min_stock'    => $minStock,
            'created_at'   => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'updated_at'   => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'editor'       => $this->session->userdata('user_data')['nik'],
        ];
        $this->db->insert($this->pneumaticTable, $pneumaticData);
    }

    /**
     * Retrieves a single pneumatic record by its unique ID.
     *
     * @param string $pneumaticId The ID of the pneumatic to retrieve.
     *
     * @return array|null The pneumatic record, or null if not found.
     */
    public function getById(string $pneumaticId): ?array
    {
        return $this->db->get_where($this->pneumaticTable, ['pneumatic_id' => $pneumaticId])->row_array();
    }

    /**
     * Updates an existing pneumatic's details.
     *
     * @param string $pneumaticId The ID of the pneumatic to update.
     *
     * @return void
     */
    public function editPneumatic(string $pneumaticId): void
    {
        $brand = strtoupper($this->input->post('brand', true));
        $type = strtoupper($this->input->post('type', true));
        $bore = (int)$this->input->post('bore', true);
        $stroke = (int)$this->input->post('stroke', true);
        $minStock = $this->input->post('min_stock') ? (int)$this->input->post('min_stock', true) : null;

        // Generate new pneumatic_id with format: pnm-{brand}-{type}-{bore}-{stroke}
        $newPneumaticId = $this->generatePneumaticId($brand, $type, $bore, $stroke);

        $pneumaticData = [
            'pneumatic_id' => $newPneumaticId,
            'brand'        => $brand,
            'type'         => $type,
            'bore'         => $bore,
            'stroke'       => $stroke,
            'min_stock'    => $minStock,
            'updated_at'   => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'editor'       => $this->session->userdata('user_data')['nik'],
        ];
        $this->db->update($this->pneumaticTable, $pneumaticData, ['pneumatic_id' => $pneumaticId]);
    }

    /**
     * Deletes a pneumatic record based on its ID.
     *
     * @param string $pneumaticId The ID of the pneumatic to delete.
     *
     * @return void
     */
    public function deletePneumatic(string $pneumaticId): void
    {
        $this->db->where('pneumatic_id', $pneumaticId)->delete($this->pneumaticTable);
    }

    /**
     * Checks if a given pneumatic ID already exists in the database.
     *
     * @param string $pneumaticId The pneumatic ID to check.
     *
     * @return bool Returns true if the ID exists, false otherwise.
     */
    public function isPneumaticIdExists(string $pneumaticId): bool
    {
        return $this->db->where('pneumatic_id', $pneumaticId)->count_all_results($this->pneumaticTable) > 0;
    }

    /**
     * Inserts multiple pneumatic records in a single batch operation.
     *
     * @param array $data An array of associative arrays containing pneumatic data.
     *
     * @return void
     */
    public function insertBatch(array $data): void
    {
        $this->db->insert_batch($this->pneumaticTable, $data);
    }

    /**
     * Retrieves all pneumatic records for export purposes.
     *
     * @return array An array of all pneumatic records.
     */
    public function getAllPneumatics(): array
    {
        $this->db->order_by('updated_at', 'DESC');
        return $this->db->get($this->pneumaticTable)->result_array();
    }

    /**
     * A private helper method to apply search and filter conditions for pneumatic retrieval.
     *
     * @param string|null $searchKeyword Optional keyword to search in pneumatic fields.
     * @param array|null  $filterKeyword Optional associative array of filters.
     *
     * @return void
     */
    private function pneumaticSearchAndFilters(?string $searchKeyword, ?array $filterKeyword): void
    {
        if ($searchKeyword && trim($searchKeyword) !== '') {
            $this->db->group_start()
                ->like('pneumatic_id', trim($searchKeyword))
                ->or_like('brand', trim($searchKeyword))
                ->or_like('type', trim($searchKeyword))
                ->or_like('bore', trim($searchKeyword))
                ->or_like('stroke', trim($searchKeyword))
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

    /**
     * Generates a pneumatic ID with format: pnm-{brand}-{type}-{bore}-{stroke}
     *
     * @param string $brand The brand name
     * @param string $type The type
     * @param int $bore The bore size
     * @param int $stroke The stroke size
     * @return string The generated pneumatic ID
     */
    private function generatePneumaticId(string $brand, string $type, int $bore, int $stroke): string
    {
        return 'pnm-' . strtolower($brand) . '-' . strtolower($type) . '-' . $bore . '-' . $stroke;
    }
}
