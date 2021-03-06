# Simple IIIF Service and Manifest

Although IIIF Presentation API allows an image resource to be a simple JPEG file, many viewers e.g. Mirador and Universal Viewer assume it to be provided by an IIIF service, which makes it hard for non technical users to try IIIF. This is a tiny experiment to provide a very simple IIIF service from plain JPEG files. It could help those who have some image files to find how IIIF works for their collection without installing full spec IIIF server.

IIIFマニフェストの画像リソースは、仕様上は単純なJPEGファイルでも良いのに、多くのビューアはIIIFサービスを前提としているため、手元の画像コレクションをIIIFマニフェストで提供したらどうなるかを簡単に試してみることができません。このツールは、本格的なIIIFサーバーを導入することなく、JPEG画像のままで最小限のIIIFサービスを提供し、主要ビュアーで確認してみることを可能にします。


## Simple IIIF Service

*iiif-simple.php* is a minimum IIIF service for JPEG pictures without predefined tiles. Because all tiles are generated on the fly, the performance will not be efficient as a large scale public service. Maybe useful for local testing purpose.

- define `IMG_ROOT` (relative path to JPEG image directory)
- prepare manifest file where service@id is set like
  `"@id": "http://example.org/{path to this script}/{image path from IMG_ROOT}"`
- the above manifest can be generated by the acompanying manifest-generator.php

Requires PHP's **GD extension**.



## A Manifest Generator

*manifest-generator.php* generates an IIIF manifest for a service that uses acompanying iiif-simple.php.

- looking up `IMG_ROOT` defined in iiif-simple.php, this scipt generates image resource objects for all JPEG images in IMG_ROOT, with service@id set to appropriate iiif-simple.php service URIs.
- you can set the image dir path with a form. On changing it, IMG_ROOT should be changed accordingly.
