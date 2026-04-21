<?php

namespace App\Traits;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Traits\PaginationResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

trait ResponseAPI
{
   /*Send Success Modifikasi
        Enhance sendSuccess untuk ngerapihin bloated code di controller
        Atribut Tambahan :
        $legacyResponse = untuk handling code2 dari controller yang pakai fungsi ini yang belum dirapihkan
        $resourceClass = Parameter untuk apakah Response yang dipassing di transform atau engga
        $customAttribute = Parameter untuk mengganti attribute 'data' dengan yang lain 
   */
    public function sendSuccess($message, $data = [], string $status = null, $legacyResponse = true, $resourceClass = null, $customAttribute = 'data'): JsonResponse
    {   
        return response()->json([
                'error'   => false,
                'status'  => $status ?: '200 OK',
                'message' => $message,
                'data'    => $legacyResponse ? $data : new PaginationResource($data, $resourceClass, $customAttribute)
            ],
            200,
            [],
            JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT
        );
    }

    /**
     * return error response.
     *
     * @param       $message
     * @param array $data
     * @param       $status
     * @param       $errorCode
     * @return \Illuminate\Http\Response
     */
    public function sendError($message, $errorCode = 500, $status = '', $data = []) : JsonResponse
    {
        return response()->json([
                'error'   => true,
                'status'  => $status ?: 'Something Wrong!',
                'message' => $message,
                'data'    => $data
            ],
            $errorCode ?: 500,
            [],
            JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT
        );
    }

    public function sendException($message, $errorCode = 500, $status = '', Exception $e = null) : JsonResponse
    {
        $datas = [
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'message' => $e->getMessage(),

        ];
        Log::info('Exception Occured!',$datas);
        return response()->json([
                'error'   => true,
                'status'  => $status ?: 'Exception Occured!',
                'message' => $message,
                'data'    => $datas
            ],
            $errorCode ?: 500,
            [],
            JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT
        );
    }


     /**
     * success response method.
     *
     * @param       $message
     * @param array $data
     * @param       $status
     * @return \Illuminate\Http\Response
     */
    public function sendSuccessCreated($message, $data = [], string $status = null) : JsonResponse
    {
        return response()->json([
                'error'   => false,
                'status'  => $status ?: '201 OK',
                'message' => $message,
                'data'    => $data
            ],
            201,
            [],
            JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT
        );
    }
    
    public function PaginatedResponse($data, $currentPage)
    {
        $isLastPage = !$data->hasMorePages();
        $prevPage = ($currentPage > 1) ? $currentPage - 1 : null;
        $nextPage = ($data->hasMorePages()) ? $currentPage + 1 : null;
        
        return [
            'prevPage' => $prevPage,
            'nextPage' => $nextPage,
            'isLast' => $isLastPage,
            'data' => $data->items()
        ];
    }

}
