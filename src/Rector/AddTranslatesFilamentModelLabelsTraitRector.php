<?php

declare(strict_types=1);

namespace MadBox99\FilamentTranslatableModelLabels\Rector;

use MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesFilamentModelLabels;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Adds the `TranslatesFilamentModelLabels` trait to Filament resource classes
 * that don't already use it, so their model labels resolve through translations.
 */
final class AddTranslatesFilamentModelLabelsTraitRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add the TranslatesFilamentModelLabels trait to Filament resource classes',
            [
                new CodeSample(
                    <<<'CODE'
                        class OrderResource extends Resource
                        {
                            protected static ?string $model = Order::class;
                        }
                        CODE,
                    <<<'CODE'
                        class OrderResource extends Resource
                        {
                            use TranslatesFilamentModelLabels;

                            protected static ?string $model = Order::class;
                        }
                        CODE,
                ),
            ],
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (! $node instanceof Class_) {
            return null;
        }

        if ($node->extends === null) {
            return null;
        }

        if (! $this->isObjectType($node, new ObjectType('Filament\Resources\Resource'))) {
            return null;
        }

        foreach ($node->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $trait) {
                if ($this->isName($trait, TranslatesFilamentModelLabels::class)) {
                    return null;
                }
            }
        }

        $node->stmts = [
            new TraitUse([new FullyQualified(TranslatesFilamentModelLabels::class)]),
            ...$node->stmts,
        ];

        return $node;
    }
}
