<?php

namespace App\Services;

use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Support\Str;

class CategoryService
{
    protected CategoryRepositoryInterface $categoryRepo;

    public function __construct(CategoryRepositoryInterface $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
    }

    public function all()
    {
        return $this->categoryRepo->all();
    }

    public function find($id)
    {
        return $this->categoryRepo->find($id);
    }

    public function create(array $data)
    {
        $data['slug'] = Str::slug($data['name']); 
        return $this->categoryRepo->create($data);
    }

    public function update($id, array $data)
    {
        $data['slug'] = Str::slug($data['name']);
        return $this->categoryRepo->update($id, $data);
    }

    public function delete($id)
    {
        return $this->categoryRepo->delete($id);
    }
}