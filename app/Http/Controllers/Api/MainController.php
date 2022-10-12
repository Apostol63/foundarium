<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Info(
 *  title="Auto API for test",
 *  version="1.0.0"
 * )
 * @OA\Tag(
 *  name="Get",
 *  description="Get methods"
 * )
 * @OA\Tag(
 *  name="Delete",
 *  description="Delete methods"
 * )
 * @OA\Tag(
 *  name="Post",
 *  description="Post methods"
 * )
 * @OA\Server(
 *  description="Auto API test server",
 *  url="http://foundarium/api"
 * )
 */


use Exception;
use App\Models\Auto;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Responses\AutoResponse;
use App\Http\Requests\AssignUserToAutoRequest;

class MainController extends Controller
{
    private $auto;

    public function __construct() {
        $this->auto = new Auto();
    }
    /**
     * @OA\Get (
     *     path="/all_autos",
     *     operationId="autosAll",
     *     tags={"Get"},
     *     description="Show all autos",
     *     @OA\Response(
     *          response=200,
     *          description="OK"
     *     ),
     * )
     */
    public function allAutos() {
        $autos = $this->auto->all();
        $response = new AutoResponse($autos);
        return $response->answer();
    }
    /**
     * @OA\Get (
     *     path="/free_autos",
     *     operationId="autosFree",
     *     tags={"Get"},
     *     description="Show all not assigned autos",
     *     @OA\Response(
     *          response=200,
     *          description="OK"
     *     ),
     * )
     */
    public function freeAutos()
    {
        $freeAutos = $this->auto->where('user_id', null)->get();

        $response = new AutoResponse($freeAutos);
        return $response->answer();
    }

    /**
     * @OA\Get (
     *     path="/assigned_autos",
     *     operationId="autosAssigned",
     *     tags={"Get"},
     *     description="Show all assigned autos",
     *     @OA\Response(
     *          response=200,
     *          description="OK"
     *     ),
     * )
     */
    public function assignedAutos() {
        $assignedAutos = $this->auto->whereNotNull('user_id')->get();

        $response = new AutoResponse($assignedAutos);
        return $response->answer();
    }
    /**
     * @OA\Delete (
     *     path="/delete_auto/{auto_id}",
     *     operationId="autoDelete",
     *     tags={"Delete"},
     *     description="Show all assigned autos",
     *     @OA\Response(
     *          response=200,
     *          description="OK"
     *     ),
     *     @OA\Parameter (
     *      name="auto_id",
     *      in="path",
     *      description="Id of auto for delere",
     *      required=true,
     *      example="1",
     *      @OA\Schema(
     *          type="integer",
     *      ),
     *     ),
     *    @OA\Response(
     *         response="default",
     *         description="Ok",
     *     ),
     * )
     */
    public function deleteAuto($auto_id) {
        try {
            $auto = $this->auto->find($auto_id);
            if (is_null($auto)) {
                throw new Exception("Автомобиль с id $auto_id не найден");
            } else {
                if (!is_null($auto->user)) {
                    throw new Exception('Невозможно удалить автомобиль, так как он закреплен за пользователем');
                } else {
                    $auto->delete();
                    return 'Автомобиль успешно удален';
                }
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    /**
     * @OA\Post(
     *     path="/assign_user_to_auto",
     *     operationId="AssignUserToAuto",
     *     tags={"Post"},
     *     @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *          mediaType="form-data",
     *          @OA\Schema(
     *          type="array",
     *           @OA\Items(
     *              @OA\Property(
     *                 property="user_id",
     *                 type="integer",
     *                 example="1"
     *              ),
     *              @OA\Property(
     *                  property="auto_id",
     *                  type="integer",
     *                  example="1"
     *              ),
     *           ),
     *          ),
     *      ),
     *     ),
     *    @OA\Response(
     *         response="default",
     *         description="Ok",
     *     ),
     * )
     *
     * @param AssignUserToAutoRequest $request
     * @return string
     */
    public function assignUserToAuto(AssignUserToAutoRequest $request) {
        $validData = $request->validated();
        $userHasAuto = $this->checkAutoByUser($validData['user_id']);
        $assignedAuto = $this->checkAssignedAuto($validData['auto_id']);
        try {
            if($userHasAuto && $userHasAuto['has_auto']) {
                throw new Exception(sprintf('Пользователь уже арендует автомобиль %s', $userHasAuto['auto']->name));
            } elseif($assignedAuto && $assignedAuto['assigned']) {
                throw new Exception(sprintf('Автомобиль %s уже занят', $assignedAuto['auto']->name));
            } elseif(!$userHasAuto && ($assignedAuto && !$assignedAuto['assigned'])) {
                $assignedAuto['auto']->user_id = $validData['user_id'];
                $res = $assignedAuto['auto']->save();
                if ($res) {
                    return sprintf('За пользователем теперь закреплен автомобиль %s', $assignedAuto['auto']->name);
                }
            }

        } catch (Exception $e) {
            return $e->getMessage();
        }

    }
    /**
     * @OA\Get (
     *     path="/unbind_user/{user_id}",
     *     operationId="userUnbind",
     *     tags={"Get"},
     *     description="Show all assigned autos",
     *     @OA\Response(
     *          response=200,
     *          description="OK"
     *     ),
     *    @OA\Parameter (
     *      name="user_id",
     *      in="path",
     *      description="Id of user for unbind",
     *      required=true,
     *      example="1",
     *      @OA\Schema(
     *          type="integer",
     *      ),
     *     ),
     * )
     */
    public function unbindUser($user_id) {
        $autoByUser = $this->auto->where('user_id', $user_id)->first();
        if (!is_null($autoByUser)) {
            $autoByUser->user_id = null;
            if ($autoByUser->save()) {
                return 'Пользователь успешно откреплен';
            }
        } else {
            return 'За пользователем нет закрепленных автомобилей';
        }
    }

    private function checkAutoByUser(int $user_id) {
        $autoByUser = $this->auto->where('user_id', $user_id)->first();
        if(!is_null($autoByUser)) {
            return [
                'auto' => $autoByUser,
                'has_auto' => (bool)$autoByUser->user_id,
            ];
        }
        return false;
    }

    private function checkAssignedAuto(int $auto_id) {
        try {
            $auto = $this->auto->find($auto_id);
            if (is_null($auto)) {
                throw new Exception("Автомобиль с id $auto_id не найден");
            } else {
                return [
                    'auto' => $auto,
                    'assigned' => (bool)$auto->user_id
                ];
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            exit(); // TODO подумать как стопнуть нормально
        }
        return false;
    }

    public function test() {
        return Auto::where('user_id', 3)->get();
    }
}
