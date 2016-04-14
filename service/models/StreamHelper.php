<?php

namespace app\models;

/**
 * Stream helpers
 *
 * @author andrey
 */
class StreamHelper {
    
    /**
     * Ataching the decompression filter to $handle
     * @param resource $handle
     */
    public static function atachDecompressionFilter($handle) {
        $params = \Yii::$app->params['compressionParameters'];
        stream_filter_append($handle, 'zlib.inflate', STREAM_FILTER_READ, $params);
    }
    
    /**
     * Ataching the compression filter to $handle
     * @param resource $handle
     */
    public static function atachCompressionFilter($handle) {
        $params = \Yii::$app->params['compressionParameters'];
        stream_filter_append($handle, 'zlib.deflate', STREAM_FILTER_WRITE, $params);
    }
    
}
