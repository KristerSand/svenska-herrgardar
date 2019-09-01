<?php namespace Sandit\Mansion\Classes\Repositories;

interface ImportRepositoryInterface
{
    public function addMansionPost(array $post_data);
    public function addEstatePost(array $post_data);
    public function isMansionPostExisting(array $post_data);
    public function isEstatePostExisting(array $post_data);
    public function deleteImportAndAllItsData(int $import_id);
    public function deleteImport(int $import_id);
}
