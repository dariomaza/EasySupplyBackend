<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\InvitationMail;
use App\Models\Cart;
use App\Models\User;
use App\Models\Workspace;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return $users;
    }

    public function store(Request $request)
    {
        $rutaImagen = null;
        if ($request->hasFile('imgSrc')) {
            // Save the image in Laravel's storage (e.g., in the storage/app/public folder)
            $rutaImagen = $request->file('imgSrc')->store('images/profileImages', 'public');
        }

        $user = User::create([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'role' => 'User',
            'plan' => $request->plan,
            'imgSrc' => $rutaImagen
        ]);

        $uuid = $user->getKey(); // Obtener el UUID del usuario creado

        $this->insertUserWorkSpace($uuid);
    }

    public function show(string $id)
    {
        $user = User::find($id);
        return $user;
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        if ($request->hasFile('imgSrc')) {
            if ($user->imgSrc) {
                Storage::disk('public')->delete($user->imgSrc);
            }
            $rutaImagen = $request->file('imgSrc')->store('images/profileImages', 'public');
            $user->imgSrc = $rutaImagen;
        }

        $user->firstName = $request->firstName;
        $user->lastName = $request->lastName;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role = 'User';

        $user->save();
    }

    public function destroy(string $id)
    {
        User::destroy($id);
    }

    public function insertUserWorkSpace(string $userId)
    {
        $user = User::findOrFail($userId);

        switch ($user->plan) {
            case 'enterprise':
                $maxUsers = 20;
                $maxStorage = 5000;
                break;
            case 'pyme':
                $maxUsers = 10;
                $maxStorage = 2000;
                break;
            default:
                $maxUsers = 1;
                $maxStorage = 500;
                break;
        }

        $workspace = Workspace::create([
            'name' => $user->firstName . ' ' . $user->lastName . '`s workspace',
            'plan' => $user->plan,
            'maxUsers' => $maxUsers,
            'maxStorage' => $maxStorage
        ]);

        $workSpaceId = $workspace->id;

        DB::table('user_workspaces')->insert([
            'user_id' => $userId,
            'workspace_id' => $workSpaceId,
            'mainUser' => true
        ]);

        Cart::create([
            'total_price' => 0,
            'workspace_id' => $workSpaceId
        ]);
    }

    public function loginUser(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();

        if ($user) {
            $decPass = password_verify($password, $user->password);
            if ($decPass) {
                return response()->json([
                    'msg' => 'Correct credentials, access allowed',
                    'user_id' => $user['id']
                ], 200);
            } else {
                return response()->json(['error' => 'The password is incorrect'], 401);
            }
        } else {
            return response()->json(['error' => 'The user does not exist'], 401);
        }
    }

    public function getWorkSpaces(String $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $workspaces = $user->workspaces;

        return response()->json($workspaces);
    }

    public function updatePassword(String $userId, Request $request)
    {
        $user = User::findOrFail($userId);
        if (!Hash::check($request->oldPassword, $user->password)) {
            return response()->json(['error' => 'The old password is not correct'], 401);
        }
        if ($request->newPassword != $request->confPassword) {
            return response()->json(['error' => 'New password does not match'], 401);
        }
        $user->password = bcrypt($request->newPassword);
        $user->save();
        return response()->json(['success' => 'Password successfully changed']);
    }

    public function inviteUserToWorkspce(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        $workspace = Workspace::findOrFail($request->workspace_id);
        $invitationEmail = $request->email;

        if (!$user) {
            $URL = 'http://easysupply.duckdns.org/invitation/' . $workspace->id;

            $invitationDetails = [
                // Información de la orden
                'name' => $workspace->name,
                'URL' => $URL,
                'message' => 'Sign up in EasySupply to access to your team´s workspace'
            ];

            // Envía el correo electrónico
            Mail::to($invitationEmail)->send(new InvitationMail($invitationDetails));

            return response()->json(['message' => 'Order email sent successfully']);
        } else {

            $URL = 'http://easysupply.duckdns.org/' . $workspace->id;

            $userId = $user->getKey();
            DB::table('user_workspaces')->insert([
                'user_id' => $userId,
                'workspace_id' => $request->workspace_id,
                'mainUser' => false,
            ]);

            $invitationDetails = [
                // Información de la orden
                'name' => $workspace->name,
                'URL' => $URL,
                'message' => 'You are now part of this workpsace, log in to your account to access all the functions of this workpsace.'
            ];

            // Envía el correo electrónico
            Mail::to($invitationEmail)->send(new InvitationMail($invitationDetails));
        }
    }

    public function registerInvitedUser(Request $request)
    {
        $rutaImagen = null;
        if ($request->hasFile('imgSrc')) {
            // Save the image in Laravel's storage (e.g., in the storage/app/public folder)
            $rutaImagen = $request->file('imgSrc')->store('images/profileImages', 'public');
        }

        $user = User::create([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'role' => 'User',
            'plan' => $request->plan,
            'imgSrc' => $rutaImagen
        ]);

        $uuid = $user->id; // Obtener el UUID del usuario creado

        $this->insertUserWorkSpace($uuid);

        DB::table('user_workspaces')->insert([
            'user_id' => $uuid,
            'workspace_id' => $request->workspaceInvited_id,
            'mainUser' => false
        ]);

        return response()->json("USER COMPLETED");
    }

    public function removeUserFromWorkspace(Request $request)
    {

        // Obtener los valores de user_id y workspace_id de la solicitud
        $userId = $request->userId;
        $workspaceId = $request->workspaceId;

        // Eliminar el registro correspondiente de la tabla users_workspaces
        DB::table('user_workspaces')
            ->where('user_id', $userId)
            ->where('workspace_id', $workspaceId)
            ->delete();

        // Responder con un mensaje de éxito
        return response()->json(['message' => 'User removed from workspace successfully'], 200);
    }
}
