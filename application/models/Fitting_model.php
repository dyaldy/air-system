<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Fitting_model for air-system.
 *
 * This model handles all database operations for the `as_fitting` table,
 * including fitting management (CRUD) and data retrieval with search,
 * pagination, and filtering capabilities.
 *
 * @package AirSystem
 * @subpackage Models
 * @category Fitting
 * @author Apparel One Indonesia
 * @version 1.0.0
 * @property CI_DB_query_builder $db
 * @property CI_Input $input
 * @property CI_Session $session
 */
class Fitting_model extends CI_Model
{
    /**
     * The name of the fitting database table used by this model.
     *
     * @var string
     */
    private string $fittingTable = 'as_fitting';

    /**
     * Retrieves a list of fittings based on search, filter, and sort criteria.
     *
     * @param int         $limit         Number of records to retrieve.
     * @param int         $start         Offset for pagination.
     * @param string|null $searchKeyword Search term for filtering results.
     * @param array|null  $filterKeyword Associative array of filter criteria.
     * @param string|null $sortKeyword   Sorting criteria in 'column-direction' format.
     *
     * @return array An array of fitting records matching the criteria.
     */
    public function getFitting(int $limit, int $start, ?string $searchKeyword = null, ?array $filterKeyword = null, ?string $sortKeyword = null): array
    {
        $this->fittingSearchAndFilters($searchKeyword, $filterKeyword);

        // Apply sorting
        if (!empty($sortKeyword)) {
            $sortParts = explode('-', $sortKeyword, 2);
            if (count($sortParts) === 2) {
                [$column, $direction] = $sortParts;
                $this->db->order_by($column, $direction);
            }
        } else {
            $this->db->order_by('fitting_id', 'ASC');
        }

        return $this->db->limit($limit, $start)->get($this->fittingTable)->result_array();
    }

    /**
     * Counts the total number of fittings matching search and filter criteria.
     *
     * @param string|null $searchKeyword Search term for filtering results.
     * @param array|null  $filterKeyword Associative array of filter criteria.
     *
     * @return int The total count of matching records.
     */
    public function countFitting(?string $searchKeyword = null, ?array $filterKeyword = null): int
    {
        $this->fittingSearchAndFilters($searchKeyword, $filterKeyword);

        return $this->db->count_all_results($this->fittingTable);
    }

    /**
     * Retrieves unique values from a specific field for filter dropdown options.
     *
     * @param string      $field         The field to get unique values from.
     * @param string|null $searchKeyword Search term for filtering results.
     * @param array|null  $filterKeyword Associative array of filter criteria.
     *
     * @return array An array of unique values from the specified field.
     */
    public function getFittingFilter(string $field, ?string $searchKeyword = null, ?array $filterKeyword = null): array
    {
        $this->db->select($field);
        $this->fittingSearchAndFilters($searchKeyword, $filterKeyword);
        $this->db->distinct()->order_by($field, 'ASC');
        $query = $this->db->get($this->fittingTable);
        return array_column($query->result_array(), $field);
    }

    /**
     * Adds a new fitting to the database.
     *
     * @return void
     */
    public function addFitting(): void
    {
        $type = strtoupper($this->input->post('type', true));
        $subtype = strtoupper($this->input->post('subtype', true));

        // Handle optional fields based on checkboxes
        $enableD1 = $this->input->post('enable_d1');
        $enableD2 = $this->input->post('enable_d2');
        $enableD3 = $this->input->post('enable_d3');
        $enableRDrat = $this->input->post('enable_r_drat');

        $D1 = $enableD1 ? (float)$this->input->post('D1', true) : null;
        $D2 = $enableD2 ? (float)$this->input->post('D2', true) : null;
        $D3 = $enableD3 ? (float)$this->input->post('D3', true) : null;
        $R_DRAT = $enableRDrat ? strtoupper($this->input->post('R_DRAT', true)) : null;

        // Generate fitting_id with format including only non-null values
        $idParts = ['fit', strtolower(str_replace('_', '-', $type)), strtolower(str_replace('_', '-', $subtype))];

        if ($D1 !== null) $idParts[] = number_format($D1, 1);
        if ($D2 !== null) $idParts[] = number_format($D2, 1);
        if ($D3 !== null) $idParts[] = number_format($D3, 1);
        if ($R_DRAT !== null) $idParts[] = str_replace('"', '', $R_DRAT);

        $fittingId = implode('-', $idParts);

        $data = [
            'fitting_id' => $fittingId,
            'type' => $type,
            'subtype' => $subtype,
            'D1' => $D1,
            'D2' => $D2,
            'D3' => $D3,
            'R_DRAT' => $R_DRAT,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'editor' => $this->session->userdata('user_data')['nik'],
        ];

        $this->db->insert($this->fittingTable, $data);
    }

    /**
     * Retrieves a fitting by its ID.
     *
     * @param string $fittingId The ID of the fitting to retrieve.
     *
     * @return array|null The fitting record, or null if not found.
     */
    public function getById(string $fittingId): ?array
    {
        return $this->db->get_where($this->fittingTable, ['fitting_id' => $fittingId])->row_array();
    }

    /**
     * Updates an existing fitting's details.
     *
     * @param string $fittingId The ID of the fitting to update.
     *
     * @return void
     */
    public function editFitting(string $fittingId): void
    {
        $type = strtoupper($this->input->post('type', true));
        $subtype = strtoupper($this->input->post('subtype', true));

        // Handle optional fields based on checkboxes
        $enableD1 = $this->input->post('enable_d1');
        $enableD2 = $this->input->post('enable_d2');
        $enableD3 = $this->input->post('enable_d3');
        $enableRDrat = $this->input->post('enable_r_drat');

        $D1 = $enableD1 ? (float)$this->input->post('D1', true) : null;
        $D2 = $enableD2 ? (float)$this->input->post('D2', true) : null;
        $D3 = $enableD3 ? (float)$this->input->post('D3', true) : null;
        $R_DRAT = $enableRDrat ? strtoupper($this->input->post('R_DRAT', true)) : null;

        // Generate new fitting_id with format including only non-null values
        $idParts = ['fit', strtolower(str_replace('_', '-', $type)), strtolower(str_replace('_', '-', $subtype))];

        if ($D1 !== null) $idParts[] = number_format($D1, 1);
        if ($D2 !== null) $idParts[] = number_format($D2, 1);
        if ($D3 !== null) $idParts[] = number_format($D3, 1);
        if ($R_DRAT !== null) $idParts[] = str_replace('"', '', $R_DRAT);

        $newFittingId = implode('-', $idParts);

        $data = [
            'fitting_id' => $newFittingId,
            'type' => $type,
            'subtype' => $subtype,
            'D1' => $D1,
            'D2' => $D2,
            'D3' => $D3,
            'R_DRAT' => $R_DRAT,
            'updated_at' => date('Y-m-d H:i:s'),
            'editor' => $this->session->userdata('user_data')['nik'],
        ];

        $this->db->where('fitting_id', $fittingId)->update($this->fittingTable, $data);
    }

    /**
     * Deletes a fitting from the database.
     *
     * @param string $fittingId The ID of the fitting to delete.
     *
     * @return void
     */
    public function deleteFitting(string $fittingId): void
    {
        $this->db->where('fitting_id', $fittingId)->delete($this->fittingTable);
    }

    /**
     * Checks if a given fitting ID already exists in the database.
     *
     * @param string $fittingId The fitting ID to check.
     *
     * @return bool Returns true if the ID exists, false otherwise.
     */
    public function isFittingIdExists(string $fittingId): bool
    {
        return $this->db->where('fitting_id', $fittingId)->count_all_results($this->fittingTable) > 0;
    }

    /**
     * Inserts multiple fitting records in a single batch operation.
     *
     * @param array $data An array of associative arrays containing fitting data.
     *
     * @return void
     */
    public function insertBatch(array $data): void
    {
        $this->db->insert_batch($this->fittingTable, $data);
    }

    /**
     * Retrieves all fitting records for export purposes.
     *
     * @return array All fitting records from the database.
     */
    public function getAllFittings(): array
    {
        return $this->db->get($this->fittingTable)->result_array();
    }

    /**
     * Retrieves unique values from a specific column for filter options.
     *
     * @param string $column The column name to get unique values from.
     *
     * @return array An array of unique values from the specified column.
     */
    public function getDistinctValues(string $column): array
    {
        $this->db->select($column)->distinct()->order_by($column);
        return array_column($this->db->get($this->fittingTable)->result_array(), $column);
    }

    /**
     * Private helper method to apply search and filter conditions.
     *
     * @param string|null $searchKeyword Search term for filtering results.
     * @param array|null  $filterKeyword Associative array of filter criteria.
     *
     * @return void
     */
    private function fittingSearchAndFilters(?string $searchKeyword, ?array $filterKeyword): void
    {
        if ($searchKeyword && trim($searchKeyword) !== '') {
            $this->db->group_start()
                ->like('fitting_id', trim($searchKeyword))
                ->or_like('type', trim($searchKeyword))
                ->or_like('subtype', trim($searchKeyword))
                ->or_like('D1', trim($searchKeyword))
                ->or_like('D2', trim($searchKeyword))
                ->or_like('D3', trim($searchKeyword))
                ->or_like('R_DRAT', trim($searchKeyword))
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
