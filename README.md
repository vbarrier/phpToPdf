# PHP to PDF

## Dompdf

* Version 1.0.2
* https://github.com/dompdf/dompdf 

Requirements:
* PHP version 7.1 or higher
* DOM extension
* MBString extension

Everything was enabled by default on my PHP (7.3.9 on MAMP)

In order to use images, I had to give permissions on assets folder
`$options->set('chroot', 'assets');`

To use fonts, I had to put the .ttf files in `dompdf/lib/fonts`, they were then processed automatically by the lib.

## Twig

* https://twig.symfony.com/doc/3.x/templates.html
* Version 3.0.2

Requirements:
* PHP version 7.2.5

