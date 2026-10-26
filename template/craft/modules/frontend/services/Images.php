<?php

namespace modules\frontend\services;

use craft\base\Image;
use craft\image\Raster;
use craft\services\Images as BaseImages;
use Imagine\Imagick\Image as ImagickImage;

/**
 * Craft's image service, plus two AVIF encoder options Craft doesn't expose.
 * Set on the Imagick instance as it loads; JPEG and WebP writes ignore them.
 */
class Images extends BaseImages
{
    /**
     * Chroma subsampling. 4:4:4 keeps colour edges crisp on UI, logos and type on flat
     * colour (and is often smaller there). Use '420' for a photography-heavy site.
     */
    public const AVIF_CHROMA = '444';

    /** aom effort, 0 (slowest, smallest) to 10. 5 is a good trade; a transform is encoded once. */
    public const AVIF_SPEED = '5';

    /**
     * @inheritdoc
     */
    public function loadImage(string $path, bool $rasterize = false, int $svgSize = 1000): Image
    {
        $image = parent::loadImage($path, $rasterize, $svgSize);

        if ($image instanceof Raster) {
            $imagineImage = $image->getImagineImage();

            if ($imagineImage instanceof ImagickImage) {
                $imagick = $imagineImage->getImagick();
                $imagick->setOption('heic:chroma', self::AVIF_CHROMA);
                $imagick->setOption('heic:speed', self::AVIF_SPEED);
            }
        }

        return $image;
    }
}
