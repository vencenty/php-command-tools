<?php
// 监听默认间隔时间
$interval = 3;
// 监听路径
$path = null;

$options = getopt('t:h', [
    'help', //帮助信息
    'watch::' // 要检测的目录,逗号分隔开
]);

foreach ($options as $option => $value) {
    switch ($option) {
        case 'help':
        case 'h':
            $manual = <<<EOF
    Description:
        -h, --help
            Know how to use
        -t
            The path to be detected can be separated by a comma, and the default value is 3
        --watch
            Specify the folder you want to listen to, multiple folders separated by commas, using an absolute path
EOF;

            fwrite(STDIN, $manual, mb_strlen($manual));
            fgets(STDIN);
            fclose(STDIN);
            exit;
        case 't':
            $interval = $value;
            break;
        case 'watch':
            $path = explode(',', $value);
            foreach ($path as $pathItem) {
                $realPath = realpath($pathItem);
                if (!is_dir($realPath)) {
                    $tips = "The specified {$pathItem} path does not exist\n";
                    fwrite(STDIN, $tips, mb_strlen($tips));
                    fgets(STDIN);
                    fclose(STDIN);
                    exit;
                }
            }
            break;
        default:
            $tips = 'Unsupported parameters';
            fwrite(STDIN, $tips, mb_strlen($tips));
            fgets(STDIN);
            fclose(STDIN);
            break;
    }

}

if (!isset($path)) {
    $tips = "Specify the folder you want to listen to, multiple folders separated by commas, using an absolute path\n";
    fwrite(STDIN, $tips, mb_strlen($tips));
    fgets(STDIN);
    fclose(STDIN);
    exit;
}


function scanDirectory($paths)
{
    $result = [];
    $collections = [];
    while ($element = array_pop($paths)) {
        $element = realpath($element);
        if (is_dir($element)) {
            $pathElements = scandir($element);
            $pathElements = array_filter($pathElements, function ($e) {
                return !in_array($e, ['.', '..']);
            });

            foreach ($pathElements as $pathElement) {
                $pathElement = $element . DIRECTORY_SEPARATOR . $pathElement;
                if (is_dir($pathElement)) {
                    array_push($paths, $pathElement);
                } else {
                    array_push($collections, $pathElement);
                }
            }
            continue;
        }
        array_push($collections, $element);
    }
    if (!$collections) {
        return [];
    }
    array_walk($collections, function ($e) use (&$result) {
        $result[$e] = md5_file($e);
    });
    return $result;
}


while (true) {
    static $previousFile = null;

    usleep($interval * 1000 * 1000);

    $files = scanDirectory($path);


    if (is_null($previousFile)) {
        $previousFile = $files;
    }
    $changedFiles = array_diff_assoc($files, $previousFile);

    $previousFile = $files;

    if ($changedFiles) {
        foreach ($changedFiles as $filePath => $hash) {
            // 不处理Swp文本
            if (pathinfo($filePath)['extension'] == 'swp') continue;
            $cmd = "gcc {$filePath} && ./a.out";
            $response = shell_exec($cmd);
            echo $response . PHP_EOL;
        }
    }
}



