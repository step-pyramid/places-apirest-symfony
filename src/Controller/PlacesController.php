<?php
// src/Controller/PlacesController.php

namespace App\Controller;

use App\Entity\Place;
use App\Repository\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/places')]
class PlacesController extends AbstractController
{
    public function __construct(
        private PlaceRepository $placeRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {}

    // GET /api/places - Get all places with optional filtering & sorting
    #[Route('', name: 'places_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // Get query parameters for filtering and sorting
        $filters = [];
        if ($request->query->get('category')) $filters['category'] = $request->query->get('category');
        if ($request->query->get('city')) $filters['city'] = $request->query->get('city');
        if ($request->query->get('rating')) $filters['rating'] = $request->query->get('rating');
        if ($request->query->get('submitted_by')) $filters['submitted_by'] = $request->query->get('submitted_by');
        
        $sort = $request->query->get('sort', 'created_at');
        $order = $request->query->get('order', 'DESC');

        try {
            $places = $this->placeRepository->findAllWithFilters($filters, $sort, $order);
            
            $placesArray = array_map(function(Place $place) {
                return $place->toArray();
            }, $places);
            
            return $this->json([
                'status' => 'success',
                'data' => $placesArray,
                'count' => count($placesArray),
                'filters' => $filters,
                'sort' => $sort,
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Failed to fetch places',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // GET /api/places/{id} - Get single place by ID
    #[Route('/{id}', name: 'places_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $place = $this->placeRepository->findById($id);
            
            if ($place) {
                return $this->json([
                    'status' => 'success',
                    'data' => $place->toArray()
                ]);
            } else {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Place not found'
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Failed to fetch place',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // POST /api/places - Create new place
    #[Route('', name: 'places_store', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        try {
            $input = json_decode($request->getContent(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Invalid JSON data'
                ], Response::HTTP_BAD_REQUEST);
            }

            // You might want to get this from authentication/session in a real app
            if (!isset($input['submitted_by'])) {
                $input['submitted_by'] = 'anonymous';
            }
            
            // Create Place entity from input
            $place = new Place($input);
            
            // Validate using Symfony's validator
            $errors = $this->validator->validate($place);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                
                return $this->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $errorMessages
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            // Save to database
            if ($this->placeRepository->save($place)) {
                return $this->json([
                    'status' => 'success',
                    'message' => 'Place created successfully',
                    'data' => $place->toArray()
                ], Response::HTTP_CREATED);
            } else {
                return $this->json([
                    'status' => 'error', 
                    'message' => 'Failed to create place'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Failed to create place',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // PUT /api/places/{id} - Update existing place
    #[Route('/{id}', name: 'places_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $input = json_decode($request->getContent(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Invalid JSON data'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Check if place exists
            $existingPlace = $this->placeRepository->findById($id);
            if (!$existingPlace) {
                return $this->json([
                    'status' => 'error', 
                    'message' => 'Place not found'
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Update the entity with new data
            if (isset($input['name'])) $existingPlace->setName($input['name']);
            if (isset($input['description'])) $existingPlace->setDescription($input['description']);
            if (isset($input['category'])) $existingPlace->setCategory($input['category']);
            if (isset($input['address'])) $existingPlace->setAddress($input['address']);
            if (isset($input['city'])) $existingPlace->setCity($input['city']);
            if (isset($input['rating'])) $existingPlace->setRating((string) $input['rating']);
            // Note: You might want to restrict who can change this field
            if (isset($input['submitted_by'])) {
                $existingPlace->setSubmittedBy($input['submitted_by']);
            }
            
            // Validate updated entity
            $errors = $this->validator->validate($existingPlace);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                
                return $this->json([
                    'status' => 'error', 
                    'message' => 'Validation failed',
                    'errors' => $errorMessages
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            // Save updates
            if ($this->placeRepository->update($existingPlace)) {
                return $this->json([
                    'status' => 'success',
                    'message' => 'Place updated successfully',
                    'data' => $existingPlace->toArray()
                ]);
            } else {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Failed to update place'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Failed to update place',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // DELETE /api/places/{id} - Delete place
    #[Route('/{id}', name: 'places_destroy', methods: ['DELETE'])]
    public function destroy(int $id): JsonResponse
    {
        try {
            // Check if place exists
            $existingPlace = $this->placeRepository->findById($id);
            if (!$existingPlace) {
                return $this->json([
                    'status' => 'error', 
                    'message' => 'Place not found'
                ], Response::HTTP_NOT_FOUND);
            }
            
            if ($this->placeRepository->remove($existingPlace)) {
                return $this->json([
                    'status' => 'success', 
                    'message' => 'Place deleted successfully'
                ]);
            } else {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Failed to delete place'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Failed to delete place',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}