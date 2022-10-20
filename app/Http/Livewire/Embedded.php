<?php

namespace App\Http\Livewire;

use Kiwilan\Steward\Enums\SocialEnum;
use Kiwilan\Steward\Services\OpenGraphService\OpenGraphItem;
use Kiwilan\Steward\Services\SocialService;
use Livewire\Component;

class Embedded extends Component
{
    public string $width = '100%';
    public string $height = '500';
    public bool $rounded = false;
    public string $url = '';

    public string $media_id = '';
    public string $title = '';
    public ?string $embedded = null;

    public bool $is_unknown = false;
    protected ?SocialEnum $type = null;
    protected ?OpenGraphItem $openGraph = null;

    public function mount()
    {
        $this->type = SocialEnum::find($this->url);
        $social = SocialService::make($this->url);

        $this->embedded = $social->getEmbedded();
        $this->is_unknown = $social->getIsUnknown();
        if ($this->is_unknown) {
            $this->openGraph = $social->getOpenGraph();
        }
    }

    public function getOpenGraph()
    {
        return $this->openGraph;
    }

    public function render()
    {
        return view('livewire.embedded');
    }
}
