<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Queue\Job;

/**
 * The AbstractJob class.
 *
 * @since  3.2
 */
interface JobInterface
{
    /**
     * getName
     *
     * @return  string
     */
    public function getName();

    /**
     * execute
     *
     * @return  void
     */
    public function execute();
}
