<?php

namespace App\Repository\Admin;

interface FrameRepositoryInterface
{
    public function getAll();
    public function getSingle($where);
    public function update($where,$update);
}
