<?php

namespace App\Repository\Admin;

interface TileRepositoryInterface
{
    public function getAll();
    public function getSingle($where);
    public function create($create);
    public function update($where,$update);
}
