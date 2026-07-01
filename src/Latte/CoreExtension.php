<?php

declare(strict_types=1);

namespace Webovac\Core\Latte;

use Latte\Compiler\Node;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Latte\Extension;


class CoreExtension extends Extension
{
	public function getTags(): array
	{
		return [
			'pageLink' => [$this, 'pageLink'],
		];
	}


	public function pageLink(Tag $tag): Node
	{
		$pageName = $tag->parser->parseUnquotedStringOrExpression();

		return new AuxiliaryNode(
			fn (PrintContext $context) => $context->format('echo $this->global->uiPresenter->pageLink(%node);', $pageName)
		);
	}
}
