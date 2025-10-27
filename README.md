# Super Nova 🌌

A modern, feature-rich PHP file uploader with a stunning Milky Way galaxy theme. Built as a single-file application with drag & drop support, URL fetching, and archive management.

## Features

- **🎨 Milky Way Galaxy Theme** - Beautiful cosmic design with purple and blue gradients
- **📁 Drag & Drop Interface** - Intuitive file upload with visual feedback
- **🌐 URL Fetching** - Download files directly from external URLs
- **👁️ File Preview** - View images and HTML files in a modal viewer
- **✏️ Rename Files** - Easy file renaming functionality
- **📦 Archive Management** - Create ZIP archives and extract files
- **🆔 Unique ID Generation** - Auto-generates unique filenames with `nova_` prefix
- **📱 Responsive Design** - Works seamlessly on desktop, tablet, and mobile
- **🇯🇵 Japanese Interface** - Complete Japanese language UI

## Requirements

- PHP 7.4 or higher
- Web server (Apache, Nginx, or PHP built-in server)
- ZipArchive extension enabled
- Write permissions on the server directory

## Installation

1. Clone this repository:
\`\`\`bash
git clone https://github.com/Sw4CyEx/Super-Nova-PHP-File-Uploader.git
cd Super-Nova-PHP-File-Uploader
\`\`\`

2. Ensure PHP ZipArchive extension is enabled:
\`\`\`bash
php -m | grep zip
\`\`\`

3. Set proper permissions:
\`\`\`bash
chmod 755 supernova.php
chmod 777 . # Allow file uploads to current directory
\`\`\`

4. Start the server:
\`\`\`bash
php -S localhost:8000
\`\`\`

5. Open your browser and navigate to:
\`\`\`
http://localhost:8000/supernova.php
\`\`\`

## Usage

### File Upload

**Method 1: Drag & Drop**
- Drag files directly into the upload zone
- Visual feedback shows when files are ready to drop

**Method 2: Click to Select**
- Click the "送信する" (Send) button
- Select files from your file browser

**Method 3: URL Fetching**
- Enter a file URL in the "URL経由取得" section
- Click "取得する" (Fetch) to download

### Allowed File Types

- `.html` - HTML documents
- `.jpg` - JPEG images
- `.gif` - GIF images
- `.png` - PNG images
- `.webp` - WebP images

### File Management

- **View (表示)** - Preview images and HTML files in a modal
- **Rename (名前変更)** - Change the filename
- **Delete (削除)** - Remove files from the server

### Archive Operations

- **Create ZIP** - Click "全ファイルをZIP化" to archive all files
- **Extract ZIP** - Upload a ZIP file and it will be automatically extracted

## File Naming

All uploaded files are automatically renamed with a unique identifier:
\`\`\`
nova_[random_string].[extension]
\`\`\`

Example: `nova_a7f3k9m2x5.jpg`

## Configuration

### Change Upload Directory

By default, files are saved in the same directory as `supernova.php`. To change this, modify the `$simpanディレクトリ` variable:

\`\`\`php
$simpanディレクトリ = __DIR__ . '/uploads/'; // Save to 'uploads' folder
\`\`\`

### Modify Allowed Extensions

Edit the `$izinkan拡張子` array:

\`\`\`php
$izinkan拡張子 = ['html', 'jpg', 'gif', 'png', 'webp', 'pdf', 'txt'];
\`\`\`

### Adjust File Size Limit

Modify your `php.ini` settings:
\`\`\`ini
upload_max_filesize = 50M
post_max_size = 50M
\`\`\`

## Security Considerations

⚠️ **Important Security Notes:**

1. **Production Use** - This uploader is designed for personal/development use. For production:
   - Add authentication/authorization
   - Implement rate limiting
   - Add CSRF protection
   - Validate file contents, not just extensions

2. **File Permissions** - Ensure uploaded files cannot be executed:
   ```apache
   # .htaccess example
   <FilesMatch "\.(html|htm)$">
       SetHandler none
   </FilesMatch>
   \`\`\`

3. **Directory Access** - Consider storing uploads outside the web root

4. **Input Validation** - The script validates file extensions, but additional validation is recommended for production

## Technical Details

- **Single File Application** - Everything in one PHP file for easy deployment
- **No Database Required** - File-based system
- **Responsive Grid Layout** - Flexbox-based design
- **Modern CSS** - Gradient backgrounds, animations, and transitions
- **Japanese Function Names** - Code uses Japanese naming conventions

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Statistics Display

The dashboard shows:
- **Total Files** - Number of uploaded files
- **Total Size** - Combined size of all files
- **Allowed Extensions** - List of permitted file types

## Troubleshooting

**Files not uploading?**
- Check directory write permissions
- Verify PHP upload limits in `php.ini`
- Check browser console for errors

**ZIP extraction not working?**
- Ensure ZipArchive extension is enabled
- Check file permissions

**Preview not showing?**
- Verify file path is correct
- Check browser console for CORS errors

## License

MIT License - Feel free to use and modify for your projects.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Author

Created with 🌟 by Ayana

---

**Note:** This project uses Japanese language for the user interface and function names. The UI text can be easily translated by modifying the string literals in the code.
