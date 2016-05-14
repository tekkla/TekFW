<?php
namespace Core\IO;

use Core\Log\Log;
use function Core\stringNormalize;

/**
 * File.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Files
{
    /**
     *
     * @var Log
     */
    private $log;

    /**
     * Constructor
     *
     * @param Log $log
     */
    public function __construct(Log $log)
    {
        $this->log = $log;
    }

    /**
     * Deletes a file or a complete directory tree
     *
     * @param string $path
     *            Full path of the file or directory to delete
     * @param boolean $clean_only
     *            Flag to delete only files while a directorystructure stays intact
     *
     * @return boolean
     */
    public function delete($path, $clean_only = false)
    {
        $path = $this->replaceDirectorySeperator($path);

        if (! file_exists($path)) {
            return true;
        }

        if (! is_dir($path)) {
            return unlink($path);
        }

        $files = scandir($path);

        foreach ($files as $item) {

            $file = $path . DIRECTORY_SEPARATOR . $item;

            if (is_dir($file)) {
                $this->delete($file);
            }
            else {
                unlink($file);
            }
        }

        return $clean_only == false ? rmdir($path) : true;
    }

    /**
     * Moves the source to the destination.
     * Both parameter have to be a full paths.On success the return value will be the destination path.
     * Otherwise it will be boolean false.
     *
     * @param string $source
     *            Path to source file
     * @param string $destination
     *            Path to destination file
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
     * Wrapper method for file_exists() plus logging feature
     *
     * @param string $filename
     *            Complete path to file
     * @param boolean $log_missing
     *            Flag to activate logging of non existant files
     *
     * @return boolean
     */
    public function exists($filename, $log_missing = false)
    {
        $filename = $this->replaceDirectorySeperator($filename);
        $exists = file_exists($filename);

        if (! $exists && $log_missing == true) {
            $this->log->file(sprintf('File "%s" not found.', $filename), - 1);
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
     * @param string $name
     *            The string to cleanup
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

        $name = stringNormalize($name);
        $name = preg_replace('/[^[:alnum:]\-]+/', $delimiter, $name);
        $name = preg_replace('/' . $delimiter . '+/', $delimiter, $name);
        $name = rtrim($name, $delimiter);

        $cleaned = isset($extension) ? $name . '.' . $extension : $name;

        return $cleaned;
    }

    /**
     * Returns an array of files inside the given directory path.
     *
     * @param string $path
     *            Directory path to get filelist from
     *
     * @throws IOException
     *
     * @return void multitype:string
     */
    public function getFilenamesFromDir($path, $recursive = false)
    {
        // Add trailing slash if missing
        if (substr($path, - 1) != '/') {
            $path .= '/';
        }

        $path = $this->replaceDirectorySeperator($path);

        $filenames = [];

        // No handle, error exception
        if (! file_exists($path)) {
            Throw new IOException(sprintf('Path "%s" not found.', $path, 2000));
        }

        $files = scandir($path);

        foreach ($files as $file) {

            // no '.' or '..'
            if ($file{0} == '.') {
                continue;
            }

            if (is_dir($path . $file) && $recursive) {
                $filenames[$file] = $this->getFilenamesFromDir($path . $file, $recursive);
            }
            else {
                continue;
            }

            // store filename
            $filenames[$file] = $file;
        }

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
        return file_exists(BASEDIR . DIRECTORY_SEPARATOR . $this->replaceDirectorySeperator($class) . '.php');
    }

    /**
     * Returns the mime type of a file by analyzing it's extension
     *
     * !!! DISCLAIMER !!!
     * This will just match the file extension to the following array.
     * It does not guarantee that the file is TRULY that of the extension that this function returns.
     *
     * @param string $file
     *            Filepath
     *
     *            Thanks to Erutan409
     * @see https://gist.github.com/Erutan409/8e774dfb2b343fe78b14
     *
     * @throws Exception
     */
    public function getMimeType($file)
    {

        // there's a bug that doesn't properly detect
        // the mime type of css files
        // https://bugs.php.net/bug.php?id=53035
        // so the following is used, instead
        // src: http://www.freeformatter.com/mime-types-list.html#mime-types-list
        $mime_type = include (__DIR__ . '/mime_types.php');

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if (isset($mime_type[$extension])) {
            return $mime_type[$extension];
        }
        else {
            Throw new IOException("Unknown file type");
        }
    }

    private function replaceDirectorySeperator($filename)
    {
        return str_replace([
            '\\',
            '/'
        ], DIRECTORY_SEPARATOR, $filename);
    }
}
