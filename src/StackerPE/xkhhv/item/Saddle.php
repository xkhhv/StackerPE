<?php

declare(strict_types = 1);

namespace StackerPE\xkhhv\item;

use pocketmine\item\Item;

class Saddle extends Item {

    public function __construct(int $meta = 0) {
        parent::__construct(self::SADDLE, $meta, "Saddle");
    }
}
