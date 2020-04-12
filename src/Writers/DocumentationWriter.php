<?php

namespace Loot\Otium\Writers;

use Loot\Otium\FetchRoute;

interface DocumentationWriter
{
    public function save(FetchRoute $fetchRoute);
}
