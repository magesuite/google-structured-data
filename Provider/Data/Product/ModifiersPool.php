<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

class ModifiersPool
{
    protected array $modifiers;

    public function __construct(array $modifiers = [])
    {
        $this->modifiers = $modifiers;
    }

    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    protected function sortResolvers(array $modifiers): array
    {
        usort($modifiers, function (array $modifierLeft, array $modifierRight) {
            if ($modifierLeft['sort_order'] == $modifierRight['sort_order']) {
                return 0;
            }

            return ($modifierLeft['sort_order'] < $modifierRight['sort_order']) ? -1 : 1;
        });

        return $modifiers;
    }
}
