<?php namespace StarlineWindows\Traits;

trait BetterSoftDeletingTrait {
    /**
     * Perform the soft-delete, by setting the deleted_at and deleted_by columns.
     * Do not use model->update to do this, as it would also update the updated_at column!
     */
    protected function runSoftDelete()
    {
        $keyName = $this->getKeyName();
        $keyValue = $this->getKey();
        $tableName = $this->getTable();

        if (array_key_exists('deleted_by', $this->attributes)) {
            $updateColumns = [$this->getDeletedAtColumn() => $this->freshTimestamp(), 'deleted_by' => Auth::user()->id];
        } else {
            $updateColumns = [$this->getDeletedAtColumn() => $this->freshTimestamp()];
        }

        // Do not use the model to do the update, as it will also update the updated_at column.
        DB::table($tableName)->where($keyName, '=', $keyValue)->update($updateColumns);
    }
}