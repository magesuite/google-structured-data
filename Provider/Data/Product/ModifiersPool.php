<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

class ModifiersPool
{
    protected array $modifiers = [];

    public function __construct(array $modifiers = []) {
        $this->modifiers = $this->sortResolvers($modifiers);
    }

    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    protected function sortResolvers(array $modifiers): array
    {
        usort($modifiers, function (array $modifierLeft, array $modifierRight): int {
            if ($modifierLeft['sort_order'] === $modifierRight['sort_order']) {
                return 0;
            }

            return ($modifierLeft['sort_order'] < $modifierRight['sort_order']) ? -1 : 1;
        });

        return $modifiers;
    }
}
