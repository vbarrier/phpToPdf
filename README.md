# PHP to PDF

Uses DOMPDF (https://github.com/dompdf/dompdf) version 1.0.2

Requirements:
* PHP version 7.1 or higher
* DOM extension
* MBString extension

Everything was enabled by default on my PHP (7.3.9 on MAMP)

In order to use images, I had to give permissions on assets folder
`$options->set('chroot', 'assets');`

To use fonts, I had to put the .ttf files in `dompdf/lib/fonts`, they were then processed automatically by the lib.

