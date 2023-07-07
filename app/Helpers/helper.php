<?php

function success($message, $data = [])
{
    return response()->json([
        'success' => $message,
        'message' => $message,
        'data' => $data
    ], 200);
}

function error($message)
{
    return response()->json([
        'error' => $message,
        'message' => $message,
    ], 500);
}

function unprocessableEntity($message)
{
    return response()->json([
        'error' => $message,
        'message' => $message,
    ], 422);
}
