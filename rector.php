<?php
declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void
{
	$parameters = $containerConfigurator->parameters();
	$parameters->set(
		Option::SETS,
		[
			SetList::PHP_74,
			SetList::TYPE_DECLARATION,
			SetList::NAMING,
			SetList::PRIVATIZATION,
			SetList::ORDER,
			SetList::PSR_4,
			SetList::EARLY_RETURN,
			SetList::DEAD_CLASSES,
			SetList::DEAD_CODE,
			SetList::DEAD_DOC_BLOCK,
			SetList::CODING_STYLE,
			SetList::CODE_QUALITY,
			SetList::CODE_QUALITY_STRICT,
			
			SetList::PHPUNIT_CODE_QUALITY,
			SetList::PHPUNIT_SPECIFIC_METHOD,
			SetList::PHPUNIT_YIELD_DATA_PROVIDER,
			SetList::PHPUNIT_CODE_QUALITY,
		]
	);
};
