<?php

namespace Luminee\Migrations\Foundations;

use Luminee\Migrations\Repositories\_BaseRepository;

trait RepoBuilder
{
    /**
     * @param $module
     * @param null $project
     * @return _BaseRepository
     */
    public function getRepo($module, $project = null)
    {
        if (!$project) $project = $this->input->getFrame('project');
        return (new _BaseRepository())
            ->bindProject($project)
            ->bindModule($module);
    }
}