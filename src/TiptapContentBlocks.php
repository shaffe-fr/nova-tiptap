<?php

namespace Manogi\Tiptap;

use Illuminate\Support\Str;

class TiptapContentBlocks
{
    public function parse($content)
    {
        $content = preg_replace_callback(
            '/<video-content-block embedcode="(.*?)" caption="(.*?)" credits="(.*?)" ratio="(.*?)" key="(.*?)"><\/video-content-block>/is',
            function ($matches) {
                return '<div class="ttcp-video-wrapper" ratio="'.$matches[4].'">
                    <div class="ttcp-video-inner">
                        <div class="ttcp-iframe-wrapper">'
                        .html_entity_decode($matches[1])
                        .'</div>
                        <div class="ttcp-below-video">
                            <div class="ttcp-caption">'.($matches[2] ?: '').'</div>
                            <div class="ttcp-credits">'.($matches[3] ?: '').'</div>
                        </div>
                    </div>
                </div>';
            },
            $content
          );

        $content = preg_replace_callback(
            '/<gallery-content-block slides="(.*?)" slidecount="(.*?)" maxcolumns="(.*?)" mode="(.*?)" key="(.*?)" imagedisk="(.*?)" imagepath="(.*?)"><\/gallery-content-block>/is',
            function ($matches) {
                $slides = json_decode(html_entity_decode($matches[1]));
                $mode = $matches[4];
                $maxcolumns = $matches[3];

                if ($mode == 'grid') {
                    $html = '<div class="ttcp-grid-wrapper"><div class="ttcp-grid-inner"><div class="ttcp-grid-stage ttcp-grid-cols-'.$maxcolumns.'">';

                    foreach ($slides as $slide) {
                        $html .= '<div class="ttcp-grid-slide">';
                        $html .= '<div class="ttcp-grid-image-wrapper">';
                        $html .= '<img class="ttcp-grid-image" src="'.$slide->src.'" />';
                        $html .= '</div>';
                        $html .= '<div class="ttcp-grid-subtext">';
                        $html .= '<div class="ttcp-grid-caption">'.$slide->caption.'</div>';
                        $html .= '<div class="ttcp-grid-credits">'.$slide->credits.'</div>';
                        $html .= '</div>';
                        $html .= '</div>';
                    }

                    $html .= '</div></div></div>';
                } elseif ($mode == 'slideshow') {
                    $html = '<div class="ttcp-slideshow-wrapper"><div class="ttcp-slideshow-inner"><div id="swiper_'.Str::random(12).'" class="swiper"><div class="swiper-wrapper">';

                    foreach ($slides as $slide) {
                        $html .= '<div class="ttcp-slideshow-slide swiper-slide">';
                        $html .= '<div class="ttcp-slideshow-image-wrapper"><div class="ttcp-slideshow-image-inner">';
                        $html .= '<img class="ttcp-slideshow-image" src="'.$slide->src.'" />';
                        $html .= '<div class="ttcp-slideshow-subtext">';
                        $html .= '<div class="ttcp-slideshow-caption">'.$slide->caption.'</div>';
                        $html .= '<div class="ttcp-slideshow-credits">'.$slide->credits.'</div>';
                        $html .= '</div>';

                        $html .= '</div></div>';
                        $html .= '</div>';
                    }

                    $html .= '</div>';

                    if (count($slides) > 1) {
                        $html .= '<div class="swiper-pagination"></div>';

                        $html .= '<div class="swiper-button-prev"></div>';
                        $html .= '<div class="swiper-button-next"></div>';
                    }

                    $html .= '</div></div></div>';
                }

                return $html;
            },
            $content
          );

        return $content;
    }
}