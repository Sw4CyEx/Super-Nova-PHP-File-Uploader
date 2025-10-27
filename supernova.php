<?php
session_start();
error_reporting(0);

// 設定
define('保存先ディレクトリ', __DIR__ . '/nova_files/');
define('許可拡張子', ['html', 'jpg', 'jpeg', 'png', 'gif', 'webp']);
define('最大サイズ', 50 * 1024 * 1024); // 50MB

// ディレクトリ作成
if (!file_exists(保存先ディレクトリ)) {
    mkdir(保存先ディレクトリ, 0755, true);
}

// ユニークID生成
function 識別子生成($拡張子) {
    return 'nova_' . bin2hex(random_bytes(8)) . '.' . $拡張子;
}

// 拡張子チェック
function 拡張子確認($ファイル名) {
    $拡張子 = strtolower(pathinfo($ファイル名, PATHINFO_EXTENSION));
    return in_array($拡張子, 許可拡張子) ? $拡張子 : false;
}

// ファイル処理
function ファイル処理() {
    $メッセージ = '';
    
    // 通常のファイル処理
    if (isset($_FILES['ファイル']) && $_FILES['ファイル']['error'] === 0) {
        $拡張子 = 拡張子確認($_FILES['ファイル']['name']);
        if ($拡張子) {
            if ($_FILES['ファイル']['size'] <= 最大サイズ) {
                $新ファイル名 = 識別子生成($拡張子);
                $保存パス = 保存先ディレクトリ . $新ファイル名;
                if (move_uploaded_file($_FILES['ファイル']['tmp_name'], $保存パス)) {
                    $メッセージ = "✨ ファイルが正常にアップロードされました: {$新ファイル名}";
                } else {
                    $メッセージ = "❌ ファイルの保存に失敗しました";
                }
            } else {
                $メッセージ = "❌ ファイルサイズが大きすぎます";
            }
        } else {
            $メッセージ = "❌ 許可されていない拡張子です";
        }
    }
    
    // URL経由処理
    if (isset($_POST['URL']) && !empty($_POST['URL'])) {
        $URL = filter_var($_POST['URL'], FILTER_VALIDATE_URL);
        if ($URL) {
            $コンテンツ = @file_get_contents($URL);
            if ($コンテンツ) {
                $元ファイル名 = basename(parse_url($URL, PHP_URL_PATH));
                $拡張子 = 拡張子確認($元ファイル名);
                if ($拡張子) {
                    $新ファイル名 = 識別子生成($拡張子);
                    $保存パス = 保存先ディレクトリ . $新ファイル名;
                    if (file_put_contents($保存パス, $コンテンツ)) {
                        $メッセージ = "✨ URLからファイルを取得しました: {$新ファイル名}";
                    }
                } else {
                    $メッセージ = "❌ URLのファイル形式が許可されていません";
                }
            } else {
                $メッセージ = "❌ URLからファイルを取得できませんでした";
            }
        }
    }
    
    // リネーム処理
    if (isset($_POST['旧名前']) && isset($_POST['新名前'])) {
        $旧パス = 保存先ディレクトリ . basename($_POST['旧名前']);
        $新名前 = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $_POST['新名前']);
        $新パス = 保存先ディレクトリ . $新名前;
        if (file_exists($旧パス) && !file_exists($新パス)) {
            if (rename($旧パス, $新パス)) {
                $メッセージ = "✨ ファイル名を変更しました: {$新名前}";
            }
        } else {
            $メッセージ = "❌ リネームに失敗しました";
        }
    }
    
    // ZIP作成
    if (isset($_POST['ZIP作成'])) {
        if (class_exists('ZipArchive')) {
            $ZIP名 = 'nova_archive_' . date('YmdHis') . '.zip';
            $ZIPパス = 保存先ディレクトリ . $ZIP名;
            $zip = new ZipArchive();
            if ($zip->open($ZIPパス, ZipArchive::CREATE) === TRUE) {
                $ファイル一覧 = glob(保存先ディレクトリ . '*');
                foreach ($ファイル一覧 as $ファイル) {
                    if (is_file($ファイル) && pathinfo($ファイル, PATHINFO_EXTENSION) !== 'zip') {
                        $zip->addFile($ファイル, basename($ファイル));
                    }
                }
                $zip->close();
                $メッセージ = "✨ ZIPファイルを作成しました: {$ZIP名}";
            }
        } else {
            $メッセージ = "❌ ZIP機能が利用できません";
        }
    }
    
    // ZIP解凍
    if (isset($_POST['解凍ファイル'])) {
        $ZIPファイル = 保存先ディレクトリ . basename($_POST['解凍ファイル']);
        if (file_exists($ZIPファイル) && class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($ZIPファイル) === TRUE) {
                $zip->extractTo(保存先ディレクトリ);
                $zip->close();
                $メッセージ = "✨ ZIPファイルを解凍しました";
            }
        } else {
            $メッセージ = "❌ 解凍に失敗しました";
        }
    }
    
    // 削除処理
    if (isset($_POST['削除ファイル'])) {
        $削除パス = 保存先ディレクトリ . basename($_POST['削除ファイル']);
        if (file_exists($削除パス) && unlink($削除パス)) {
            $メッセージ = "✨ ファイルを削除しました";
        }
    }
    
    if (isset($_GET['表示']) && !empty($_GET['表示'])) {
        $表示ファイル = 保存先ディレクトリ . basename($_GET['表示']);
        if (file_exists($表示ファイル)) {
            $拡張子 = strtolower(pathinfo($表示ファイル, PATHINFO_EXTENSION));
            $mime_types = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'html' => 'text/html'
            ];
            if (isset($mime_types[$拡張子])) {
                header('Content-Type: ' . $mime_types[$拡張子]);
                readfile($表示ファイル);
                exit;
            }
        }
    }
    
    return $メッセージ;
}

$メッセージ = ファイル処理();

// ファイル一覧取得
function ファイル一覧取得() {
    $ファイル配列 = [];
    $一覧 = glob(保存先ディレクトリ . '*');
    foreach ($一覧 as $パス) {
        if (is_file($パス)) {
            $ファイル配列[] = [
                '名前' => basename($パス),
                'サイズ' => filesize($パス),
                '日時' => filemtime($パス),
                '拡張子' => pathinfo($パス, PATHINFO_EXTENSION)
            ];
        }
    }
    usort($ファイル配列, function($a, $b) {
        return $b['日時'] - $a['日時'];
    });
    return $ファイル配列;
}

$ファイル一覧 = ファイル一覧取得();

function サイズ変換($バイト) {
    $単位 = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($バイト >= 1024 && $i < 3) {
        $バイト /= 1024;
        $i++;
    }
    return round($バイト, 2) . ' ' . $単位[$i];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Nova - ファイルマネージャー</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            color: #e0e0e0;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(ellipse at 20% 30%, rgba(138, 43, 226, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 70%, rgba(75, 0, 130, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(72, 61, 139, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        
        /* コンテナの幅制限を削除し、パディングで調整 */
        .container {
            position: relative;
            z-index: 1;
            padding: 20px;
            width: 100%;
        }
        
        .header {
            background: rgba(30, 20, 60, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(138, 43, 226, 0.3);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 8px 32px rgba(138, 43, 226, 0.2);
        }
        
        .logo {
            font-size: clamp(24px, 5vw, 32px);
            font-weight: bold;
            background: linear-gradient(135deg, #da22ff 0%, #9733ee 50%, #4568dc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 30px rgba(138, 43, 226, 0.5);
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo::before {
            content: '✦';
            font-size: clamp(28px, 6vw, 36px);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.1); }
        }
        
        /* ダッシュボードグリッドを調整してより広く使用 */
        .dashboard {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            align-items: start;
        }
        
        @media (min-width: 768px) {
            .dashboard {
                grid-template-columns: 320px 1fr;
            }
        }
        
        @media (min-width: 1024px) {
            .dashboard {
                grid-template-columns: 350px 1fr;
            }
        }
        
        @media (min-width: 1440px) {
            .dashboard {
                grid-template-columns: 380px 1fr;
            }
        }
        
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .card {
            background: rgba(30, 20, 60, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(138, 43, 226, 0.3);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        @media (min-width: 768px) {
            .card {
                padding: 25px;
            }
        }
        
        .card-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #da22ff;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        @media (min-width: 768px) {
            .card-title {
                font-size: 18px;
            }
        }
        
        .drop-zone {
            border: 2px dashed rgba(138, 43, 226, 0.5);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: rgba(15, 12, 41, 0.4);
            margin-bottom: 15px;
        }
        
        .drop-zone:hover {
            border-color: rgba(138, 43, 226, 0.8);
            background: rgba(138, 43, 226, 0.1);
        }
        
        .drop-zone.drag-over {
            border-color: #da22ff;
            background: rgba(138, 43, 226, 0.2);
            transform: scale(1.02);
        }
        
        .drop-zone-icon {
            font-size: 48px;
            margin-bottom: 10px;
            opacity: 0.7;
        }
        
        .drop-zone-text {
            color: #b8b8d1;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .drop-zone-hint {
            color: #8888aa;
            font-size: 12px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #b8b8d1;
        }
        
        input[type="file"],
        input[type="text"],
        input[type="url"] {
            width: 100%;
            padding: 12px 15px;
            background: rgba(15, 12, 41, 0.6);
            border: 1px solid rgba(138, 43, 226, 0.4);
            border-radius: 8px;
            color: #e0e0e0;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        input[type="file"]:hover,
        input[type="text"]:hover,
        input[type="url"]:hover {
            border-color: rgba(138, 43, 226, 0.6);
        }
        
        input[type="file"]:focus,
        input[type="text"]:focus,
        input[type="url"]:focus {
            outline: none;
            border-color: #da22ff;
            box-shadow: 0 0 15px rgba(138, 43, 226, 0.3);
        }
        
        .btn {
            width: 100%;
            padding: 12px 20px;
            background: linear-gradient(135deg, #da22ff 0%, #9733ee 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(138, 43, 226, 0.4);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(138, 43, 226, 0.6);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #4568dc 0%, #b06ab3 100%);
        }
        
        .message {
            padding: 15px;
            background: rgba(138, 43, 226, 0.2);
            border: 1px solid rgba(138, 43, 226, 0.4);
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .file-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .file-item {
            background: rgba(15, 12, 41, 0.6);
            border: 1px solid rgba(138, 43, 226, 0.3);
            border-radius: 10px;
            padding: 15px;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 15px;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        @media (min-width: 768px) {
            .file-item {
                grid-template-columns: auto 1fr auto;
            }
        }
        
        .file-item:hover {
            border-color: rgba(138, 43, 226, 0.6);
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(138, 43, 226, 0.2);
        }
        
        .file-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #da22ff 0%, #9733ee 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            color: white;
        }
        
        @media (min-width: 768px) {
            .file-icon {
                font-size: 20px;
            }
        }
        
        .file-info {
            flex: 1;
            min-width: 0;
        }
        
        .file-name {
            font-size: 13px;
            font-weight: 600;
            color: #e0e0e0;
            margin-bottom: 5px;
            word-break: break-all;
        }
        
        @media (min-width: 768px) {
            .file-name {
                font-size: 14px;
            }
        }
        
        .file-meta {
            font-size: 11px;
            color: #8888aa;
        }
        
        @media (min-width: 768px) {
            .file-meta {
                font-size: 12px;
            }
        }
        
        .file-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            grid-column: 1 / -1;
            margin-top: 10px;
        }
        
        @media (min-width: 768px) {
            .file-actions {
                grid-column: auto;
                margin-top: 0;
                flex-wrap: nowrap;
            }
        }
        
        .btn-small {
            padding: 6px 12px;
            font-size: 11px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            white-space: nowrap;
        }
        
        @media (min-width: 768px) {
            .btn-small {
                font-size: 12px;
            }
        }
        
        .btn-view {
            background: rgba(138, 43, 226, 0.8);
            color: white;
        }
        
        .btn-rename {
            background: rgba(69, 104, 220, 0.8);
            color: white;
        }
        
        .btn-delete {
            background: rgba(220, 69, 104, 0.8);
            color: white;
        }
        
        .btn-extract {
            background: rgba(104, 220, 69, 0.8);
            color: white;
        }
        
        .btn-small:hover {
            transform: scale(1.05);
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }
        
        @media (min-width: 768px) {
            .stats {
                gap: 15px;
            }
        }
        
        .stat-card {
            background: rgba(15, 12, 41, 0.6);
            border: 1px solid rgba(138, 43, 226, 0.3);
            border-radius: 10px;
            padding: 12px;
            text-align: center;
        }
        
        @media (min-width: 768px) {
            .stat-card {
                padding: 15px;
            }
        }
        
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #da22ff;
            margin-bottom: 5px;
        }
        
        @media (min-width: 768px) {
            .stat-value {
                font-size: 24px;
            }
        }
        
        .stat-label {
            font-size: 11px;
            color: #8888aa;
        }
        
        @media (min-width: 768px) {
            .stat-label {
                font-size: 12px;
            }
        }
        
        .allowed-formats {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 10px;
        }
        
        @media (min-width: 768px) {
            .allowed-formats {
                gap: 8px;
            }
        }
        
        .format-badge {
            padding: 4px 10px;
            background: rgba(138, 43, 226, 0.3);
            border: 1px solid rgba(138, 43, 226, 0.5);
            border-radius: 5px;
            font-size: 11px;
            color: #da22ff;
            font-weight: 600;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            position: relative;
            margin: 5% auto;
            padding: 0;
            max-width: 90%;
            max-height: 85vh;
            background: rgba(30, 20, 60, 0.95);
            border: 2px solid rgba(138, 43, 226, 0.5);
            border-radius: 15px;
            box-shadow: 0 10px 50px rgba(138, 43, 226, 0.3);
            overflow: hidden;
        }
        
        .modal-header {
            padding: 20px;
            background: rgba(15, 12, 41, 0.8);
            border-bottom: 1px solid rgba(138, 43, 226, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            color: #da22ff;
            font-size: 18px;
            font-weight: 600;
        }
        
        .modal-close {
            background: rgba(220, 69, 104, 0.8);
            border: none;
            color: white;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .modal-close:hover {
            background: rgba(220, 69, 104, 1);
            transform: scale(1.1);
        }
        
        .modal-body {
            padding: 20px;
            max-height: calc(85vh - 80px);
            overflow: auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .modal-body img {
            max-width: 100%;
            max-height: calc(85vh - 120px);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }
        
        .modal-body iframe {
            width: 100%;
            height: calc(85vh - 120px);
            border: none;
            border-radius: 8px;
            background: white;
        }
        
        @media (max-width: 767px) {
            .container {
                padding: 15px;
            }
            
            .drop-zone {
                padding: 20px;
            }
            
            .drop-zone-icon {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Super Nova</div>
        </div>
        
        <?php if ($メッセージ): ?>
            <div class="message"><?= htmlspecialchars($メッセージ) ?></div>
        <?php endif; ?>
        
        <div class="dashboard">
            <div class="sidebar">
                <!-- ドラッグ&ドロップ対応のファイル送信 -->
                <div class="card">
                    <div class="card-title">📁 ファイル送信</div>
                    <form method="POST" enctype="multipart/form-data" id="送信フォーム">
                        <div class="drop-zone" id="ドロップゾーン">
                            <div class="drop-zone-icon">📤</div>
                            <div class="drop-zone-text">ファイルをドラッグ&ドロップ</div>
                            <div class="drop-zone-hint">またはクリックして選択</div>
                        </div>
                        <input type="file" name="ファイル" id="ファイル入力" style="display: none;">
                        <button type="submit" class="btn" id="送信ボタン">送信する</button>
                    </form>
                    <div class="allowed-formats">
                        <?php foreach (許可拡張子 as $拡張子): ?>
                            <span class="format-badge">.<?= $拡張子 ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- URL経由 -->
                <div class="card">
                    <div class="card-title">🌐 URL経由取得</div>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">ファイルURL</label>
                            <input type="url" name="URL" placeholder="https://example.com/image.jpg" required>
                        </div>
                        <button type="submit" class="btn btn-secondary">取得する</button>
                    </form>
                </div>
                
                <!-- ZIP作成 -->
                <div class="card">
                    <div class="card-title">📦 アーカイブ</div>
                    <form method="POST">
                        <button type="submit" name="ZIP作成" class="btn btn-secondary">全ファイルをZIP化</button>
                    </form>
                </div>
            </div>
            
            <div class="main-content">
                <div class="card">
                    <div class="stats">
                        <div class="stat-card">
                            <div class="stat-value"><?= count($ファイル一覧) ?></div>
                            <div class="stat-label">総ファイル数</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?= サイズ変換(array_sum(array_column($ファイル一覧, 'サイズ'))) ?></div>
                            <div class="stat-label">合計サイズ</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?= count(許可拡張子) ?></div>
                            <div class="stat-label">対応形式</div>
                        </div>
                    </div>
                    
                    <div class="card-title">📂 ファイル一覧</div>
                    <div class="file-list">
                        <?php if (empty($ファイル一覧)): ?>
                            <div style="text-align: center; padding: 40px; color: #8888aa;">
                                ファイルがありません
                            </div>
                        <?php else: ?>
                            <?php foreach ($ファイル一覧 as $ファイル): ?>
                                <div class="file-item">
                                    <div class="file-icon"><?= strtoupper(substr($ファイル['拡張子'], 0, 3)) ?></div>
                                    <div class="file-info">
                                        <div class="file-name"><?= htmlspecialchars($ファイル['名前']) ?></div>
                                        <div class="file-meta">
                                            <?= サイズ変換($ファイル['サイズ']) ?> • 
                                            <?= date('Y/m/d H:i', $ファイル['日時']) ?>
                                        </div>
                                    </div>
                                    <div class="file-actions">
                                        <!-- 表示ボタン追加 -->
                                        <?php if (in_array($ファイル['拡張子'], ['jpg', 'jpeg', 'png', 'gif', 'webp', 'html'])): ?>
                                            <button class="btn-small btn-view" onclick="表示('<?= htmlspecialchars($ファイル['名前']) ?>', '<?= $ファイル['拡張子'] ?>')">表示</button>
                                        <?php endif; ?>
                                        <button class="btn-small btn-rename" onclick="リネーム('<?= htmlspecialchars($ファイル['名前']) ?>')">名前変更</button>
                                        <?php if ($ファイル['拡張子'] === 'zip'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="解凍ファイル" value="<?= htmlspecialchars($ファイル['名前']) ?>">
                                                <button type="submit" class="btn-small btn-extract">解凍</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="削除ファイル" value="<?= htmlspecialchars($ファイル['名前']) ?>">
                                            <button type="submit" class="btn-small btn-delete" onclick="return confirm('削除しますか？')">削除</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- モーダル追加 -->
    <div id="表示モーダル" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="モーダルタイトル">ファイル表示</div>
                <button class="modal-close" onclick="モーダル閉じる()">&times;</button>
            </div>
            <div class="modal-body" id="モーダル本体"></div>
        </div>
    </div>
    
    <script>
        const ドロップゾーン = document.getElementById('ドロップゾーン');
        const ファイル入力 = document.getElementById('ファイル入力');
        const 送信フォーム = document.getElementById('送信フォーム');
        
        ドロップゾーン.addEventListener('click', () => {
            ファイル入力.click();
        });
        
        ファイル入力.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                送信フォーム.submit();
            }
        });
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(イベント名 => {
            ドロップゾーン.addEventListener(イベント名, 防止, false);
            document.body.addEventListener(イベント名, 防止, false);
        });
        
        function 防止(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(イベント名 => {
            ドロップゾーン.addEventListener(イベント名, () => {
                ドロップゾーン.classList.add('drag-over');
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(イベント名 => {
            ドロップゾーン.addEventListener(イベント名, () => {
                ドロップゾーン.classList.remove('drag-over');
            }, false);
        });
        
        ドロップゾーン.addEventListener('drop', (e) => {
            const ファイル群 = e.dataTransfer.files;
            if (ファイル群.length > 0) {
                ファイル入力.files = ファイル群;
                送信フォーム.submit();
            }
        }, false);
        
        function リネーム(旧名前) {
            const 新名前 = prompt('新しいファイル名を入力してください:', 旧名前);
            if (新名前 && 新名前 !== 旧名前) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="旧名前" value="${旧名前}">
                    <input type="hidden" name="新名前" value="${新名前}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function 表示(ファイル名, 拡張子) {
            const モーダル = document.getElementById('表示モーダル');
            const モーダルタイトル = document.getElementById('モーダルタイトル');
            const モーダル本体 = document.getElementById('モーダル本体');
            
            モーダルタイトル.textContent = ファイル名;
            モーダル本体.innerHTML = '';
            
            const URL = '?表示=' + encodeURIComponent(ファイル名);
            
            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(拡張子)) {
                const img = document.createElement('img');
                img.src = URL;
                img.alt = ファイル名;
                モーダル本体.appendChild(img);
            } else if (拡張子 === 'html') {
                const iframe = document.createElement('iframe');
                iframe.src = URL;
                モーダル本体.appendChild(iframe);
            }
            
            モーダル.style.display = 'block';
        }
        
        function モーダル閉じる() {
            document.getElementById('表示モーダル').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const モーダル = document.getElementById('表示モーダル');
            if (event.target === モーダル) {
                モーダル閉じる();
            }
        }
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                モーダル閉じる();
            }
        });
    </script>
</body>
</html>
