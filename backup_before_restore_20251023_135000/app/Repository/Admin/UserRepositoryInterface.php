<?php

namespace App\Repository\Admin;

interface UserRepositoryInterface
{
    public function getAll();
    public function getSingle($where);
    public function update($where,$update);
}
