<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * AbcHeaderFieldTable stores and manages unique header field values (e.g., composer, book).
 * Future: supports matching, suggestions, and corrections during ABC processing.
 */
class AbcHeaderFieldTable
{
    /** @var array */
    protected $fields = [];

    /**
     * Add a value for a header field (e.g., composer, book)
     * @param string $field
     * @param string $value
     */
    public function addFieldValue($field, $value)
    {
        if (!isset($this->fields[$field])) {
            $this->fields[$field] = [];
        }
        if (!in_array($value, $this->fields[$field], true)) {
            $this->fields[$field][] = $value;
        }
    }

    /**
     * Get all values for a header field
     * @param string $field
     * @return array
     */
    public function getFieldValues($field)
    {
        return $this->fields[$field] ?? [];
    }

    /**
     * Get all fields and their values
     * @return array
     */
    public function getAllFields()
    {
        return $this->fields;
    }

    /**
     * Edit a field value (replace old with new)
     */
    public function editFieldValue($field, $oldValue, $newValue)
    {
        $values = $this->getFieldValues($field);
        $key = array_search($oldValue, $values, true);
        if ($key !== false) {
            $this->fields[$field][$key] = $newValue;
            return true;
        }
        return false;
    }

    /**
     * Delete a field value
     */
    public function deleteFieldValue($field, $value)
    {
        $values = $this->getFieldValues($field);
        $key = array_search($value, $values, true);
        if ($key !== false) {
            unset($this->fields[$field][$key]);
            $this->fields[$field] = array_values($this->fields[$field]);
            return true;
        }
        return false;
    }
}
