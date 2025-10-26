<?php

namespace PocketArC\FixPhpPostInput;

use Riverline\MultiPartParser\StreamedPart;

/**
 * If your $_POST/$_FILES are empty and they shouldn't be, this library fixes them.
 */
class FixPhpPostInput {

    protected $temp_files = [];

    protected function edit_array($array, $name, $value) {
        $name_parts = explode("[", $name);
        $array_part = &$array;

        $is_last = false;
        $i = 1;
        foreach ($name_parts as $name_part) {
            if ($i == count($name_parts)) {
                $is_last = true;
            }

            $name_part = str_ireplace(']', "", $name_part);

            if (!isset($array_part[$name_part])) {
                if (empty($name_part)) {
                    $array_part[] = ($is_last ? $value : []);
                } else {
                    $array_part[$name_part] = ($is_last ? $value : []);
                }

            }

            if (!$is_last) {
                $array_part = &$array_part[$name_part];
            }

            $i++;
        }

        return $array;
    }

    protected function store_temp($contents) {
        $tmp_name = tempnam(sys_get_temp_dir(), "file-");
        $this->temp_files[] = $tmp_name;
        $result = file_put_contents($tmp_name, $contents);

        if (strlen($contents) !== $result) {
            throw new \RuntimeException("Could not store temp file in $tmp_name.");
        }

        return $tmp_name;
    }

    protected function map_name_to_array($name, $array) {
        if (substr($name, -2) == "[]") {
            if (isset($array[substr($name, 0, -2)])) {
                return $array[substr($name, 0, -2)];
            }
        }

        if (isset($array[$name])) {
            return $array[$name];
        }


        $return = null;
        $code = 'if(isset($array' . implode("", $name) . ')) {$return = $array' . implode("", $name) . ';} else { $return = null; }';
        eval($code);
        return $return;
    }

    function __construct(&$post = null, &$files = null, $server = null, $php_input = null) {

        if ($post === null) {
            $post = &$_POST;
        }

        if ($files === null) {
            $files = &$_FILES;
        }

        if ($php_input === null) {
            $php_input = file_get_contents('php://input');
        }

        if ($server === null) {
            $server = $_SERVER;
        }

        if (count($post) == 0) {
            $is_post = false;
            foreach ($server as $key => $value) {
                $search = "REQUEST_METHOD";
                if (substr($key, -strlen($search)) == $search) {
                    if ($value == "POST") {
                        $is_post = true;
                    }
                }
            }

            if ($is_post) {
                if (!empty($php_input)) {
                    $is_multipart = (stristr($server["CONTENT_TYPE"], "multipart/form-data") !== false);
                    $is_json = (stristr($server["CONTENT_TYPE"], "application/json") !== false);

                    if ($is_json) {
                        $post = json_decode($php_input, true);
                    } elseif ($is_multipart) {
						$content = "Content-Type: " . $server["CONTENT_TYPE"] . "\n\n" . $php_input;
	                    $stream = fopen('php://temp', 'rw');
	                    fwrite($stream, $content);
	                    rewind($stream);

                        $document = new StreamedPart($stream);
                        foreach ($document->getParts() as $part) {
                            if ($part->isFile()) {
                                $size = strlen($part->getBody());

                                $data = [
                                    "name" => $part->getFileName(),
                                    "type" => $size ? $part->getMimeType() : "",
                                    "tmp_name" => $size ? $this->store_temp($part->getBody()) : "",
                                    "error" => $size ? UPLOAD_ERR_OK : UPLOAD_ERR_NO_FILE,
                                    "size" => $size,
                                ];

                                foreach ($data as $key => $value) {
                                    $name = explode("[", $part->getName(), 2);
                                    $name = $name[0] . "[$key][" . $name[1];
                                    $files = $this->edit_array($files, $name, $value);
                                }
                            } else {
                                $post = $this->edit_array($post, $part->getName(), $part->getBody());
                            }
                        }
                    } else {
                        parse_str($php_input, $post);
                    }
                }
            }
        }
    }

    function __destruct() {
        foreach ($this->temp_files as $temp_file) {
            if (file_exists($temp_file)) {
                unlink($temp_file);
            }
        }
    }

}