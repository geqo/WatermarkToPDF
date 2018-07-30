# WatermarkToPDF
Place your watermark on every page in PDF-file.
## Installation
```bash
composer require geqo/watermarktopdf
```
## Usage
```php
$file = new \Geqo\WatermarkToPDF(
  __DIR__ . '/watermark.png', 
  __DIR__ . '/document.pdf', 
  __DIR__ . '/result.pdf'
);
$file->execute();
```
