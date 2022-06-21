<?php

namespace Nue\Backup\Http\Controllers;

use Novay\Nue\Facades\Nue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BackupController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Backup';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() 
    {
        view()->share([
            'title' => $this->title
        ]);
    }

    /**
     * Index interface.
     *
     * @param Request $request
     *
     * @return Illuminate\View\View
     */
    public function index() 
    {
        $backup = new \Nue\Backup\Backup();

        return view('nue-backup::index', [
            'backups' => $backup->getExists()
        ]);
    }

    /**
     * Download a backup zip file.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function download(Request $request)
    {
        $disk = $request->get('disk');
        $file = $request->get('file');

        $storage = Storage::disk($disk);

        if ($storage->exists($file)) {
            return $storage->download($file);
        }

        return response('', 404);
    }

    /**
     * Run `backup:run` command.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function run()
    {
        try {
            ini_set('max_execution_time', 300);

            // start the backup process
            Artisan::call('backup:run');

            $output = Artisan::output();

            return response()->json([
                'status'  => true,
                'message' => $output . '
Refresh page to see the backup files!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete a backup file.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $disk = Storage::disk($request->get('disk'));
        $file = $request->get('file');

        if ($disk->exists($file)) {
            $disk->delete($file);

            return response()->json([
                'status'  => true,
                'message' => 'Delete succeeded !',
            ]);
        }

        return response()->json([
            'status'  => false,
            'message' => 'Delete failed !',
        ]);
    }
}