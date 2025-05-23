<?php

declare(strict_types=1);

namespace Webovac\Core\Model\File;

use App\Model\File\File;
use App\Model\FileTranslation\FileTranslation;
use App\Model\Language\LanguageData;


trait CoreFile
{
	public const string DEFAULT_ICON = 'PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MTIgNTEyIj48IS0tISBGb250IEF3ZXNvbWUgUHJvIDYuNS4yIGJ5IEBmb250YXdlc29tZSAtIGh0dHBzOi8vZm9udGF3ZXNvbWUuY29tIExpY2Vuc2UgLSBodHRwczovL2ZvbnRhd2Vzb21lLmNvbS9saWNlbnNlIChDb21tZXJjaWFsIExpY2Vuc2UpIENvcHlyaWdodCAyMDI0IEZvbnRpY29ucywgSW5jLiAtLT48ZGVmcz48c3R5bGU+LmZhLXNlY29uZGFyeXtvcGFjaXR5Oi40fTwvc3R5bGU+PC9kZWZzPjxwYXRoIGNsYXNzPSJmYS1zZWNvbmRhcnkiIGQ9Ik0zNTIgMjU2YzAgMjIuMS0xLjIgNDMuNi0zLjMgNjRIMTYzLjNjLTIuMi0yMC40LTMuMy00MS45LTMuMy02NHMxLjItNDMuNiAzLjMtNjRIMzQ4LjdjMi4yIDIwLjQgMy4zIDQxLjkgMy4zIDY0em0yOC44LTY0SDUwMy45YzUuMyAyMC41IDguMSA0MS45IDguMSA2NHMtMi44IDQzLjUtOC4xIDY0SDM4MC44YzIuMS0yMC42IDMuMi00MiAzLjItNjRzLTEuMS00My40LTMuMi02NHptMTEyLjYtMzJIMzc2LjdDMzY2LjkgOTYuNyAzNDcuNSA0My4zIDMyMi41IDguN2M3Ny44IDIwLjkgMTQxIDc3LjUgMTcwLjkgMTUxLjN6bS0xNDkuMSAwSDE2Ny43YzYuMS0zNi43IDE1LjUtNjkuMiAyNy4xLTk1LjVjMTAuNS0yMy44IDIyLjItNDEuMiAzMy42LTUyLjFjNi44LTYuNSAxMi44LTEwLjIgMTguMS0xMi4yYzMuMi0uMSA2LjMtLjIgOS41LS4yczYuMyAuMSA5LjUgLjJjNS4zIDEuOSAxMS40IDUuNyAxOC4xIDEyLjJjMTEuNCAxMC45IDIzLjEgMjguMyAzMy42IDUyLjFjMTEuNiAyNi4zIDIxIDU4LjggMjcuMSA5NS41em0tMjA5LjEgMEgxOC42QzQ4LjUgODYuMyAxMTEuNiAyOS42IDE4OS41IDguN2MtMjUgMzQuNi00NC40IDg4LTU0LjIgMTUxLjN6TTguMSAxOTJIMTMxLjJjLTIuMSAyMC42LTMuMiA0Mi0zLjIgNjRzMS4xIDQzLjQgMy4yIDY0SDguMUMyLjggMjk5LjUgMCAyNzguMSAwIDI1NnMyLjgtNDMuNSA4LjEtNjR6TTE5NC44IDQ0Ny41Yy0xMS42LTI2LjMtMjEtNTguOC0yNy4xLTk1LjVIMzQ0LjNjLTYuMSAzNi43LTE1LjUgNjkuMi0yNy4xIDk1LjVjLTEwLjUgMjMuOC0yMi4yIDQxLjItMzMuNiA1Mi4xYy02LjggNi41LTEyLjggMTAuMi0xOC4xIDEyLjJjLTMuMiAuMS02LjMgLjItOS41IC4ycy02LjMtLjEtOS41LS4yYy01LjMtMS45LTExLjQtNS43LTE4LjEtMTIuMmMtMTEuNC0xMC45LTIzLjEtMjguMy0zMy42LTUyLjF6TTEzNS4zIDM1MmM5LjkgNjMuMyAyOS4yIDExNi43IDU0LjIgMTUxLjNDMTExLjYgNDgyLjQgNDguNSA0MjUuNyAxOC42IDM1MkgxMzUuM3ptMzU4LjEgMGMtMjkuOCA3My43LTkzIDEzMC40LTE3MC45IDE1MS4zYzI1LTM0LjYgNDQuNC04OCA1NC4yLTE1MS4zSDQ5My40eiIvPjxwYXRoIGNsYXNzPSJmYS1wcmltYXJ5IiBkPSJNMzQ0LjMgMzUyYy02LjEgMzYuNy0xNS41IDY5LjItMjcuMSA5NS41Yy0xMC41IDIzLjgtMjIuMiA0MS4yLTMzLjYgNTIuMWMtNi44IDYuNS0xMi44IDEwLjItMTguMSAxMi4yYzE5LjctLjcgMzguNy0zLjYgNTctOC42YzI1LTM0LjYgNDQuMy04OCA1NC4yLTE1MS4zSDQ5My40YzQuMi0xMC4zIDcuNy0yMSAxMC41LTMySDM4MC44YzIuMS0yMC42IDMuMi00MiAzLjItNjRzLTEuMS00My40LTMuMi02NEg1MDMuOWMtMi44LTExLTYuNC0yMS43LTEwLjUtMzJIMzc2LjdDMzY2LjkgOTYuNyAzNDcuNSA0My4zIDMyMi41IDguN2MtMTguMy00LjktMzcuNC03LjgtNTctOC42YzUuMyAxLjkgMTEuNCA1LjcgMTguMSAxMi4yYzExLjQgMTAuOSAyMy4xIDI4LjMgMzMuNiA1Mi4xYzExLjYgMjYuMyAyMSA1OC44IDI3LjEgOTUuNUgxNjcuN2M2LjEtMzYuNyAxNS41LTY5LjIgMjcuMS05NS41YzEwLjUtMjMuOCAyMi4yLTQxLjIgMzMuNi01Mi4xYzYuOC02LjUgMTIuOC0xMC4yIDE4LjEtMTIuMmMtMTkuNyAuNy0zOC43IDMuNi01NyA4LjZjLTI1IDM0LjYtNDQuNCA4OC01NC4yIDE1MS4zSDE4LjZjLTQuMiAxMC4zLTcuNyAyMS0xMC41IDMySDEzMS4yYy0yLjEgMjAuNi0zLjIgNDItMy4yIDY0czEuMSA0My40IDMuMiA2NEg4LjFjMi44IDExIDYuNCAyMS43IDEwLjUgMzJIMTM1LjNjOS45IDYzLjMgMjkuMiAxMTYuNyA1NC4yIDE1MS4zYzE4LjMgNC45IDM3LjQgNy44IDU3IDguNmMtNS4zLTEuOS0xMS40LTUuNy0xOC4xLTEyLjJjLTExLjQtMTAuOS0yMy4xLTI4LjMtMzMuNi01Mi4xYy0xMS42LTI2LjMtMjEtNTguOC0yNy4xLTk1LjVIMzQ0LjN6bTcuNy05NmMwIDIyLjEtMS4yIDQzLjYtMy4zIDY0SDE2My4zYy0yLjItMjAuNC0zLjMtNDEuOS0zLjMtNjRzMS4yLTQzLjYgMy4zLTY0SDM0OC43YzIuMiAyMC40IDMuMyA0MS45IDMuMyA2NHoiLz48L3N2Zz4=';
	public const string TYPE_FILE = 'file';
	public const string TYPE_IMAGE = 'image';
	public const string TYPE_SVG = 'svg';
	public const string TYPE_VIDEO = 'video';
	public const int SIZE_LIMIT = 1920;


	public function getTranslation(LanguageData $language): ?FileTranslation
	{
		return $this->translations->toCollection()->getBy(['language' => $language->id]);
	}


	public function getDefaultIdentifier(): ?string
	{
		return $this->type === File::TYPE_IMAGE ? $this->modernIdentifier : $this->identifier;
	}


	public function getIconIdentifier(): ?string
	{
		if ($this->type === File::TYPE_FILE) {
			return null;
		}
		return $this->type === File::TYPE_SVG ? $this->compatibleIdentifier : $this->identifier;
	}


	public function getBackgroundIdentifier(): ?string
	{
		if ($this->type === File::TYPE_FILE) {
			return null;
		}
		return $this->modernIdentifier;
	}


	public function getLimitedWidth(): ?int
	{
		if ($this->type === File::TYPE_FILE) {
			return null;
		}
		if ($this->height <= File::SIZE_LIMIT && $this->width <= File::SIZE_LIMIT) {
			return $this->width;
		}
		$limitRatio = 1920 / max($this->width, $this->height);
		return (int) round($this->width * $limitRatio);
	}


	public function getLimitedHeight(): ?int
	{
		if ($this->type === File::TYPE_FILE) {
			return null;
		}
		if ($this->height <= File::SIZE_LIMIT && $this->width <= File::SIZE_LIMIT) {
			return $this->height;
		}
		$limitRatio = 1920 / max($this->width, $this->height);
		return (int) round($this->height * $limitRatio);
	}
}
