<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Manifold_model for air-system.
 *
 * This model handles all database operations for the `as_manifold` table,
 * including manifold management (CRUD) and data retrieval with search,
 * pagination, and filtering capabilities.
 *
 * @package AirSystem
 * @subpackage Models
 * @category Manifold
 * @author Apparel One Indonesia
 * @version 1.0.0
 * @property CI_DB_query_builder $db
 * @property CI_Input $input
 * @property CI_Session $session
 */
class Manifold_model extends CI_Model
{
    /**
     * The name of the manifold database table used by this model.
     *
     * @var string
     */
    private $manifoldTable = 'as_manifold';

    /**
     * Retrieves a list of manifolds based on search, filter, and sort criteria.
     *
     * @param int         $limit         Number of records to retrieve.
     * @param int         $start         Offset for pagination.
     * @param string|null $searchKeyword Optional keyword to search in manifold fields.
     * @param array|null  $filterKeyword Optional associative array of filters.
     * @param string|null $sortKeyword   Optional sort criteria in the format "field-order".
     *
     * @return array An array of manifold records matching the criteria.
     */
    public function getManifold(int $limit, int $start, ?string $searchKeyword = null, ?array $filterKeyword = null, ?string $sortKeyword = null): array
    {
        $this->manifoldSearchAndFilters($searchKeyword, $filterKeyword);

        if ($sortKeyword && strpos($sortKeyword, '-') !== false) {
            [$field, $order] = explode('-', $sortKeyword, 2);
            $this->db->order_by($field, $order);
        } else {
            $this->db->order_by('updated_at', 'DESC');
        }

        return $this->db->get($this->manifoldTable, $limit, $start)->result_array();
    }

    /**
     * Counts the number of manifold records matching search and filter criteria.
     *
     * @param string|null $searchKeyword Optional keyword to search in manifold fields.
     * @param array|null  $filterKeyword Optional associative array of filters.
     *
     * @return int The total number of records matching the criteria.
     */
    public function countManifold(?string $searchKeyword = null, ?array $filterKeyword = null): int
    {
        $this->manifoldSearchAndFilters($searchKeyword, $filterKeyword);
        return $this->db->count_all_results($this->manifoldTable);
    }

    /**
     * Retrieves distinct values of a specific field for filter dropdowns.
     *
     * @param string      $field         The field to retrieve distinct values from.
     * @param string|null $searchKeyword Optional keyword to search in manifold fields.
     * @param array|null  $filterKeyword Optional associative array of filters.
     *
     * @return array An array of distinct values.
     */
    public function getManifoldFilter(string $field, ?string $searchKeyword = null, ?array $filterKeyword = null): array
    {
        $this->db->select($field);
        $this->manifoldSearchAndFilters($searchKeyword, $filterKeyword);
        $this->db->distinct()->order_by($field, 'ASC');
        $query = $this->db->get($this->manifoldTable);
        return array_column($query->result_array(), $field);
    }

    /**
     * Inserts a new manifold record into the database.
     *
     * @return void
     */
    public function addManifold(): void
    {
        $block = (int)$this->input->post('block', true);
        $minStock = $this->input->post('min_stock') ? (int)$this->input->post('min_stock', true) : null;

        // Generate manifold_id with format: mnf-{block}
        $manifoldId = $this->generateManifoldId($block);

        $manifoldData = [
            'manifold_id' => $manifoldId,
            'block'       => $block,
            'min_stock'   => $minStock,
            'created_at'  => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'updated_at'  => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'editor'      => $this->session->userdata('user_data')['nik'],
        ];
        $this->db->insert($this->manifoldTable, $manifoldData);
    }

    /**
     * Retrieves a single manifold record by its unique ID.
     *
     * @param string $manifoldId The ID of the manifold to retrieve.
     *
     * @return array|null The manifold record, or null if not found.
     */
    public function getById(string $manifoldId): ?array
    {
        return $this->db->get_where($this->manifoldTable, ['manifold_id' => $manifoldId])->row_array();
    }

    /**
     * Updates an existing manifold's details.
     *
     * @param string $manifoldId The ID of the manifold to update.
     *
     * @return void
     */
    public function editManifold(string $manifoldId): void
    {
        $block = (int)$this->input->post('block', true);
        $minStock = $this->input->post('min_stock') ? (int)$this->input->post('min_stock', true) : null;

        // Generate new manifold_id with format: mnf-{block}
        $newManifoldId = $this->generateManifoldId($block);

        $manifoldData = [
            'manifold_id' => $newManifoldId,
            'block'       => $block,
            'min_stock'   => $minStock,
            'updated_at'  => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'editor'      => $this->session->userdata('user_data')['nik'],
        ];
        $this->db->update($this->manifoldTable, $manifoldData, ['manifold_id' => $manifoldId]);
    }

    /**
     * Deletes a manifold record based on its ID.
     *
     * @param string $manifoldId The ID of the manifold to delete.
     *
     * @return void
     */
    public function deleteManifold(string $manifoldId): void
    {
        $this->db->where('manifold_id', $manifoldId)->delete($this->manifoldTable);
    }

    /**
     * Checks if a given manifold ID already exists in the database.
     *
     * @param string $manifoldId The manifold ID to check.
     *
     * @return bool Returns true if the ID exists, false otherwise.
     */
    public function isManifoldIdExists(string $manifoldId): bool
    {
        return $this->db->where('manifold_id', $manifoldId)->count_all_results($this->manifoldTable) > 0;
    }

    /**
     * Inserts multiple manifold records in a single batch operation.
     *
     * @param array $data An array of associative arrays containing manifold data.
     *
     * @return void
     */
    public function insertBatch(array $data): void
    {
        $this->db->insert_batch($this->manifoldTable, $data);
    }

    /**
     * Retrieves all manifold records for export purposes.
     *
     * @return array An array of all manifold records.
     */
    public function getAllManifolds(): array
    {
        $this->db->order_by('updated_at', 'DESC');
        return $this->db->get($this->manifoldTable)->result_array();
    }

    /**
     * A private helper method to apply search and filter conditions for manifold retrieval.
     *
     * @param string|null $searchKeyword Optional keyword to search in manifold fields.
     * @param array|null  $filterKeyword Optional associative array of filters.
     *
     * @return void
     */
    private function manifoldSearchAndFilters(?string $searchKeyword, ?array $filterKeyword): void
    {
        if ($searchKeyword && trim($searchKeyword) !== '') {
            $this->db->group_start()
                ->like('manifold_id', trim($searchKeyword))
                ->or_like('block', trim($searchKeyword))
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
     * Generates a manifold ID with format: mnf-{block}
     *
     * @param int $block The block number
     * @return string The generated manifold ID
     */
    private function generateManifoldId(int $block): string
    {
        return 'mnf-' . $block;
    }
}
