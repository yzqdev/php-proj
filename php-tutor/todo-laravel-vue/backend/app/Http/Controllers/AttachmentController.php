<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Task;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function __construct(private Request $request)
    {

    }

    /**
    * @OA\Post(
    * path="/api/task/{id}/attachment",
    * operationId="AttachFile",
    * tags={"AttachFileToTask"},
    * summary="Attach file to task",
    * description="Attach file to task",
    * security={{"sanctum":{}}},
    *     @OA\Parameter(
    *         name="id",
    *         description="Id of the task",
    *         in="path",
    *         required=true
    *     ),
    *     @OA\RequestBody(
    *           required=true,
    *           description="Attachment",
    *           @OA\MediaType(
    *               mediaType="multipart/form-data",
    *               @OA\Schema(
    *                   @OA\Property(property="file", type="string", format="binary"),
    *                   required={"file"}
    *               ),
    *           ),
    *    ),
    *    @OA\Response(
    *          response=200,
    *          description="Upload successful",
    *          @OA\JsonContent(
    *           ref="#/components/schemas/Attachment"
    *          ),
    *       ),
    * )
    **/
    public function upload($id)
    {
        $this->request->validate([
            'file' => 'required|mimes:mp4,jpeg,jpg,png,csv,txt,doc,docx,pdf|max:5000'
        ]);

        $task = Task::findOrfail($id);
        $this->checkUserIsOwner($task);
        $uploadedFile = $this->request->file('file');
        $oExtension = $uploadedFile->getClientOriginalExtension();
        $oFilename = $uploadedFile->getClientOriginalName();
        $filename = preg_replace('/[^a-zA-Z0-9_-]+/', '-', substr(strtolower($oFilename), 0, 15)).'-'.time().'.'.$oExtension;

        // upload the file
        $uploadedFile->storeAs('uploads', $filename, 'public');

        $path = Storage::putFileAs(
            'attachments', $uploadedFile, $filename
        );

        $attachment = Attachment::factory()->create([
            'filename' => $filename,
            'type' => $oExtension,
            'task_id' => $task->id,
            'path' => $path
        ]);

        $task->attachments()->save($attachment);

    }

    public function delete($taskId, $attachmentId)
    {
        $task = Task::findOrFail($taskId);
        $this->checkUserIsOwner($task);
        $attachment = Attachment::where([
            'id' => $attachmentId,
            'task_id' => $taskId
        ])->first();

        if (!$attachment) {
            abort(404, 'Attachment not found');
        }

        Storage::delete($attachment->filename);
        $attachment->delete();
    }

    public function download($taskId, $attachmentId)
    {
        Task::findOrFail($taskId);
        $attachment = Attachment::where([
            'id' => $attachmentId,
            'task_id' => $taskId
        ])->first();

        if (!$attachment) {
            abort(404, 'Attachment not found');
        }

        return Storage::download($attachment->path, basename($attachment->path), [
            'Content-Description: File Transfer',
            'Content-Disposition: attachment',
            'filename:' . basename($attachment->path)
        ]);
    }

    private function checkUserIsOwner(Task $task): void
    {
        // check if user is not the creator of task
        if ($task->user()->first()->id !== Auth::user()->id){
            // throw 401 exception error
            abort(401, 'You can only upload files on your own task');
        }
    }

    /**
    * @OA\Get(
    * path="/api/task/{id}/attachment",
    * operationId="GetAttachedFiles",
    * tags={"AttachFileToTask"},
    * summary="Get the attached files of task",
    * description="Get the attached files of task",
    *    @OA\Parameter(
    *         name="id",
    *         description="Id of the task",
    *         in="path",
    *         required=true
    *    ),
    *     @OA\Response(
    *          response=200,
    *          description="List of attachments",
    *          @OA\JsonContent(
    *               @OA\Property(
    *                   property="data",
    *                   type="array",
    *                   description="List of attachments",
    *                   @OA\Items(
    *                       ref="#/components/schemas/Attachment"
    *                   )
    *               ),
    *          ),
    *    ),
    * )
    **/
    public function getAttachmentsByTask($taskId)
    {
        $task = Task::findOrFail($taskId);
        $this->checkUserIsOwner($task);
        $attachments = Attachment::where('task_id', $taskId)->get();
        return response([
            'data' => $attachments
        ]);
    }
}
