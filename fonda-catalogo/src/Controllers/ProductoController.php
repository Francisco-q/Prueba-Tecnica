<?php

namespace FondaJuanita\Controllers;

use FondaJuanita\Repositories\ProductoRepository;

class ProductoController
{
    private $repository;

    public function __construct(ProductoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        // Listar todos los productos
        return $this->repository->getAll();
    }

    public function show($id)
    {
        // Mostrar un producto por ID
        return $this->repository->getById($id);
    }

    public function store($data)
    {
        // Crear un nuevo producto
        return $this->repository->create($data);
    }

    public function update($id, $data)
    {
        // Actualizar un producto existente
        return $this->repository->update($id, $data);
    }

    public function destroy($id)
    {
        // Eliminar un producto
        return $this->repository->delete($id);
    }
}
