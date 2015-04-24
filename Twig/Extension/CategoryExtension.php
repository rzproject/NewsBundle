<?php

namespace Rz\NewsBundle\Twig\Extension;

class CategoryExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('rz_render_category', array($this, 'renderTree')),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    public function renderTree($form)
    {
        $tree = $form->children ?:  null;

        $options = array(
            'decorate' => false,
            'rootOpen' => '<ul>',
            'rootClose' => '</ul>',
            'childOpen' => '<li>',
            'childClose' => '</li>',
        );

        $nestedTree = array();

        $nestedTree = array();
        $l = 0;

        if (count($tree) > 0) {
            // Node Stack. Used to help building the hierarchy
            $stack = array();
            foreach ($tree as $child) {
                $item = $child;
//                $item[$this->childrenIndex] = array();
//                 Number of stack items
                $l = count($stack);
                var_dump($l);
                if($l > 0) {
                    var_dump($stack[$l - 1]);
                }
                // Check if we're dealing with different levels
//                while($l > 0 && $stack[$l - 1][$config['level']] >= $item[$config['level']]) {
//                    array_pop($stack);
//                    $l--;
//                }
                // Stack is empty (we are inspecting the root)
                if ($l == 0) {
                    // Assigning the root child
                    $i = count($nestedTree);
                    $nestedTree[$i] = $item;
                    $stack[] = &$nestedTree[$i];
                } else {
//                     Add child to parent
                    $i = count($stack[$l - 1][$this->childrenIndex]);
                    $stack[$l - 1][$this->childrenIndex][$i] = $item;
                    $stack[] = &$stack[$l - 1][$this->childrenIndex][$i];
                }
            }
        }

        die('rommel');

        return $nestedTree;


        return;
    }

    public function getName()
    {
        return 'rz_render_category';
    }
}
