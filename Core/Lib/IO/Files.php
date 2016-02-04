<?php
namespace Core\Lib\IO;

use Core\Lib\Logging\Logging;
use Core\Lib\Traits\StringTrait;

/**
 * File.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Files
{
    use StringTrait;

    /**
     *
     * @var Logging
     */
    private $log;

    /**
     * Constructor
     *
     * @param Logging $log
     */
    public function __construct(Logging $log)
    {
        $this->log = $log;
    }

    /**
     * Creates a directory by given path.
     * If the directory exitsts the return
     * value will be the given path. If not, the path is created and the return
     * value boolean false/true
     *
     * @param string $path Path and name of dir
     *
     * @return string bool
     */
    public function createDir($path)
    {
        // does dir exist? use it?
        return file_exists($path) ? $path : mkdir($path);
    }

    /**
     * Deletes recursive a given dir inclusive all files and folders within it.
     *
     * @param $dirname Path to the dir
     *
     * @throws IOException
     * @throws IOException
     *
     * @return boolean
     */
    public function deleteDir($dirname)
    {
        if (is_dir($dirname)) {
            $dir_handle = opendir($dirname);
        }
        else {
            Throw new IOException('The dirname parameter is not a valid directory');
        }

        if (! $dir_handle) {
            Throw new IOException('Directory handle couldn\'t be created.');
        }

        while (($file = readdir($dir_handle)) != false) {
            if ($file != "." && $file != "..") {
                if (! is_dir($dirname . "/" . $file)) {
                    unlink($dirname . "/" . $file);
                }
                else {
                    $this->deleteDir($dirname . '/' . $file);
                }
            }
        }

        closedir($dir_handle);
        rmdir($dirname);

        return true;
    }

    /**
     * Moves the source to the destination.
     * Both parameter have to be a full paths.On success the return value will be the destination path.
     * Otherwise it will be boolean false.
     *
     * @param string $source Path to source file
     * @param string $destination Path to destination file
     *
     * @return string boolean
     */
    public function moveFile($source, $destination)
    {
        if (copy($source, $destination)) {
            unlink($source);
            return $destination;
        }
        else {
            return false;
        }
    }

    /**
     * Same as php's core move_uploaded_file extended with destination file exists
     * check.
     * Fails this check an exception is throwm.
     *
     * @param string $source
     * @param string $destination
     * @param bool $check_exists
     *
     * @throws IOException
     *
     * @return boolean
     */
    public function moveUploadedFile($source, $destination, $check_exists = true)
    {
        if ($check_exists == true && $this->exists($destination)) {
            Throw new IOException('File already exits', 2001);
        }

        return move_uploaded_file($source, $destination);
    }

    /**
     * Wrapper method for file_exists() which throws an error.
     *
     * @param string $full_path Complete path to file
     * @param string $log_missing
     *
     * @return boolean
     *
     * @throws \RuntimeException
     */
    public function exists($full_path, $log_missing = false)
    {
        $exists = file_exists($full_path);

        if (! $exists && $log_missing == true) {
            $this->log->file(sprintf('File "%s" not found.', $full_path), - 1);
        }

        return $exists;
    }

    /**
     * Converts a filesize (bytes as integer) into a human readable string format.
     * For example: 1024 => 1 KByte
     *
     * @param int $bytes
     *
     * @throws IOException
     *
     * @return string unknown
     */
    public function convFilesize($bytes)
    {
        if (! $bytes == '0' . $bytes) {
            Throw new IOException('Wrong parameter type');
        }

        if ($bytes > 0) {
            $unit = intval(log($bytes, 1024));
            $units = [
                'Bytes',
                'KByte',
                'MByte',
                'GByte'
            ];

            if (array_key_exists($unit, $units) === true) {
                return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
            }
        }

        return $bytes;
    }

    /**
     * Cleans up a filename string from all characters which can make trouble on filesystems.
     *
     * @param string $name The string to cleanup
     * @param string $delimiter
     *
     * @return string
     */
    public function cleanFilename($name, $delimiter = '-')
    {
        // The fileextension should not be normalized.
        if (strrpos($name, '.') !== false) {
            list ($name, $extension) = explode('.', $name);
        }

        $name = $this->normalizeString($name);
        $name = preg_replace('/[^[:alnum:]\-]+/', $delimiter, $name);
        $name = preg_replace('/' . $delimiter . '+/', $delimiter, $name);
        $name = rtrim($name, $delimiter);

        $cleaned = isset($extension) ? $name . '.' . $extension : $name;

        return $cleaned;
    }

    /**
     * Returns an array of files inside the given directory path.
     *
     * @param string $path Directory path to get filelist from
     *
     * @throws IOException
     *
     * @return void multitype:string
     */
    public function getFilenamesFromDir($path)
    {
        // Add trailing slash if missing
        if (substr($path, - 1) != '/') {
            $path .= '/';
        }

        // Output array for filenames
        $filenames = [];

        // Get dir handle
        $handle = opendir($path);

        // No handle, error exception
        if ($handle === false) {
            Throw new IOException(sprintf('Path "%s" not found.', $path, 2000));
        }

        while (($file = readdir($handle)) !== false) {

            // no '.' or '..' or dir
            if ('.' == $file || '..' == $file || is_dir($path . $file)) {
                continue;
            }

            // store filename
            $filenames[] = $file;
        }

        closedir($handle);

        return $filenames;
    }

    /**
     * Returns uploads
     *
     * @return array
     */
    public function getUploads()
    {
        return $_FILES['files'];
    }

    /**
     * Transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in case of 2M)
     *
     * @var $size string Size inas string (like '2M')
     *
     * @return int Size in bytes
     */
    public function convertPHPSizeToBytes($size)
    {
        $suffix = substr($size, - 1);
        $value = substr($size, 0, - 1);

        switch (strtoupper($suffix)) {
            case 'P':
                $value *= 1024;
            case 'T':
                $value *= 1024;
            case 'G':
                $value *= 1024;
            case 'M':
                $value *= 1024;
            case 'K':
                $value *= 1024;
                break;
        }

        return $value;
    }

    /**
     * Returns the maximum size for uploads in bytes.
     *
     * @return int
     */
    public function getMaximumFileUploadSize()
    {
        return $this->convertPHPSizeToBytes(ini_get('upload_max_filesize'));
    }

    /**
     * Checks for existing class file of a given classname.
     *
     * Takes care of namespaces.
     *
     * @param string $class
     *
     * @return boolean
     */
    public function checkClassFileExists($class)
    {
        return file_exists(BASEDIR . '/' . str_replace('\\', '/', $class) . '.php');
    }
}
