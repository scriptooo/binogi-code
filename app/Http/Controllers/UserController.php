<?php

namespace App\Http\Controllers;

use App\Mappers\UserMapper;
use App\Models\User\User;
use App\Repositories\UserRepository;
use App\Support\Requests\UserStoreRequest;
use App\Support\Requests\UserUpdateRequest;
use Exception;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct(private UserRepository $userRepository, private UserMapper $userMapper)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Show user",
     *     description="Show user",
     *     @OA\Parameter(
     *          name="user",
     *          in="path",
     *          description="ID of user",
     *          required=true,
     *          example=1,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="User Details",
     *         @OA\JsonContent(ref="#/components/schemas/UserMapper"),
     *     ),
     * )
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        
        $users = User::select('id', 'name', 'email', 'nickname')->get();
        return \Response::json(
            $this->userMapper->single($user),
            200,
            []
        );
    }

    

    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Create user",
     *     description="Create user",
     *     @OA\RequestBody(
     *         @OA\JsonContent(ref="#/components/schemas/UserStoreRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User created",
     *         @OA\JsonContent(ref="#/components/schemas/UserMapper"),
     *     ),
     *     @OA\Response(response=400, description="User cannot be created"),
     *     @OA\Response(response=422, description="Failed validation of given params"),
     * )
     *
     * @param UserStoreRequest $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function store(UserStoreRequest $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'nickname' => 'required|string|max:30|unique:users,nickname', // Validate the new field
        ]);

        $user = $this->userRepository->create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        return \Response::json($this->userMapper->single($user));
    }


    /**
     * @OA\Put(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Update user",
     *     description="Update user",
     *     @OA\Parameter(
     *          name="user",
     *          in="path",
     *          description="ID of user",
     *          required=true,
     *          example=1,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(ref="#/components/schemas/UserUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User after the update",
     *         @OA\JsonContent(ref="#/components/schemas/UserMapper"),
     *     ),
     *     @OA\Response(response=422, description="Failed validation of given params"),
     * )
     *
     * @param UserUpdateRequest $request
     * @param User              $user
     *
     * @return JsonResponse
     */
    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {

        $request->validate([
            'nickname' => 'string|max:30|unique:users,nickname,' . $user->id,
        ]);

        $data = [
            'name'     => trim($request->input('name')),
            'email'    => trim($request->input('email')),
            'password' => Hash::make(trim($request->input('password')) ?: null),
        ];

        $user->update(['nickname' => $request->input('nickname')]);

        $user->fill($data)->save();

        return \Response::json($this->userMapper->single($user));
    }
}
