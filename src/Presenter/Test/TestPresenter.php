<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter\Test;

use App\Model\Orm;
use Nette\Application\UI\Presenter;
use Nette\Database\Explorer;
use Nette\DI\Attributes\Inject;
use Nextras\Orm\Model\Model;


class TestPresenter extends Presenter
{
	#[Inject] public Orm $orm;
	#[Inject] public Explorer $explorer;


	public function renderDefault(): void
	{
		$this->template->person = $this->orm->personRepository->getById(420707);
//		$this->template->person = $this->explorer->table('person')->get(420707);
	}
}