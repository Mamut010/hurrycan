<?php
namespace App\Constants;

final class MimeType
{
    const ANY = '*/*';

    const APPLICATION_ANY = 'application/*';
    const APPLICATION_PDF = 'application/pdf';
    const APPLICATION_JSON = 'application/json';
    const APPLICATION_ZIP = 'application/zip';
    const APPLICATION_OCTET_STREAM = 'application/octet-stream';
    const APPLICATION_X_WWW_FORM_URLENCODED = 'application/x-www-form-urlencoded';

    const TEXT_PLAIN = 'text/plain';
    const TEXT_HTML = 'text/html';
    const TEXT_CSS = 'text/css';
    
    const MULTIPART_FORM_DATA = 'multipart/form-data';

    const IMAGE_JPEG = 'image/jpeg';
    const IMAGE_PNG = 'image/png';
    const IMAGE_GIF = 'image/gif';
    const IMAGE_X_ICON = 'image/x-icon';
    
    const AUDIO_MPEG = 'audio/mpeg';
    const VIDEO_MP4 = 'video/mp4';
}
