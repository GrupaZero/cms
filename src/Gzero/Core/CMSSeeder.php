<?php namespace Gzero\Core;

use Gzero\Entity\BlockType;
use Gzero\Model\Content;
use Gzero\Model\ContentTranslation;
use Gzero\Model\Lang;
use Gzero\Model\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class CMSSeeder
 *
 * @package    Gzero\Core
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @SuppressWarnings("PHPMD")
 */
class CMSSeeder extends Seeder {

    /**
     * This function run all seeds
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @return void
     * @SuppressWarnings("PHPMD")
     */
    public function run()
    {
        $lang = Lang::find('en');
        if (!$lang) {
            $lang = new Lang(['code' => 'en', 'i18n' => 'en_US', 'isEnabled' => 1]);
            $lang->save();
        }

        $content = new Content(['path' => 'xyz']);
        $content->save();
        $translation        = new ContentTranslation(['langCode' => 'en']);
        $translation->title = 'Simple title';
        $translation->body  = 'Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo,
            tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.
             Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. ';
        $content->translations()->save($translation);

        // Create user
        $user = User::find(1);
        if (!$user) {
            User::create(
                [
                    'email'     => 'a@a.pl',
                    'firstName' => 'John',
                    'lastName'  => 'Doe',
                    'password'  => Hash::make('test')

                ]
            );
        }
    }
}
