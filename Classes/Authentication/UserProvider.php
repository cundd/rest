<?php


/*
 * rest
 * @author daniel
 * Date: 14.09.13
 * Time: 19:33
 */


namespace Cundd\Rest\Authentication;

/**
 * Alias for the concrete implementation of UserProviderInterface
 *
 * Workaround for the ObjectManager to find the implementation without the full TypoScript loaded
 */
class UserProvider extends UserProvider\FeUserProvider
{
}
