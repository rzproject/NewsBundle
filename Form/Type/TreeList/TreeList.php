<?php

namespace Rz\NewsBundle\Form\Type\TreeList;

use Symfony\Component\Form\Extension\Core\ChoiceList;
use Symfony\Component\Form\FormConfigBuilder;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * A choice list for choices of arbitrary data types.
 *
 * Choices and labels are passed in two arrays. The indices of the choices
 * and the labels should match. Choices may also be given as hierarchy of
 * unlimited depth by creating nested arrays. The title of the sub-hierarchy
 * can be stored in the array key pointing to the nested array. The topmost
 * level of the hierarchy may also be a \Traversable.
 *
 * <code>
 * $choices = array(true, false);
 * $labels = array('Agree', 'Disagree');
 * $choiceList = new ChoiceList($choices, $labels);
 * </code>
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TreeList extends ChoiceList
{
    /**
     * The choices with their indices as keys.
     *
     * @var array
     */
    private $choices = array();

    /**
     * The choice values with the indices of the matching choices as keys.
     *
     * @var array
     */
    private $values = array();

    /**
     * The preferred view objects as hierarchy containing also the choice groups
     * with the indices of the matching choices as bottom-level keys.
     *
     * @var array
     */
    private $preferredViews = array();

    /**
     * The non-preferred view objects as hierarchy containing also the choice
     * groups with the indices of the matching choices as bottom-level keys.
     *
     * @var array
     */
    private $remainingViews = array();

    public function __construct($choices, array $labels, array $preferredChoices = array())
    {
        if (!is_array($choices) && !$choices instanceof \Traversable) {
            throw new UnexpectedTypeException($choices, 'array or \Traversable');
        }

        $this->initialize($choices, $labels, $preferredChoices);
    }

    /**
     * Initializes the list with choices.
     *
     * Safe to be called multiple times. The list is cleared on every call.
     *
     * @param array|\Traversable $choices          The choices to write into the list.
     * @param array              $labels           The labels belonging to the choices.
     * @param array              $preferredChoices The choices to display with priority.
     */
    protected function initialize($choices, array $labels, array $preferredChoices)
    {
        $this->choices = array();
        $this->values = array();
        $this->preferredViews = array();
        $this->remainingViews = array();

        $this->addChoices(
            $this->preferredViews,
            $this->remainingViews,
            $choices,
            $labels,
            $preferredChoices
        );
    }

    /**
     * Recursively adds the given choices to the list.
     *
     * @param array              $bucketForPreferred The bucket where to store the preferred
     *                                               view objects.
     * @param array              $bucketForRemaining The bucket where to store the
     *                                               non-preferred view objects.
     * @param array|\Traversable $choices            The list of choices.
     * @param array              $labels             The labels corresponding to the choices.
     * @param array              $preferredChoices   The preferred choices.
     *
     * @throws InvalidArgumentException     If the structures of the choices and labels array do not match.
     * @throws InvalidConfigurationException If no valid value or index could be created for a choice.
     */
    protected function addChoices(array &$bucketForPreferred, array &$bucketForRemaining, $choices, array $labels, array $preferredChoices)
    {
        // Add choices to the nested buckets
        foreach ($choices as $group => $choice) {
            if (!array_key_exists($group, $labels)) {
                throw new InvalidArgumentException('The structures of the choices and labels array do not match.');
            }

            if (is_array($choice)) {
                // Don't do the work if the array is empty
                if (count($choice) > 0) {
                    $this->addChoiceGroup(
                        $group,
                        $bucketForPreferred,
                        $bucketForRemaining,
                        $choice,
                        $labels[$group],
                        $preferredChoices
                    );
                }
            } else {
                $this->addChoice(
                    $bucketForPreferred,
                    $bucketForRemaining,
                    $choice,
                    $labels[$group],
                    $preferredChoices
                );
            }
        }
    }

    /**
     * Recursively adds a choice group.
     *
     * @param string $group              The name of the group.
     * @param array  $bucketForPreferred The bucket where to store the preferred
     *                                   view objects.
     * @param array  $bucketForRemaining The bucket where to store the
     *                                   non-preferred view objects.
     * @param array  $choices            The list of choices in the group.
     * @param array  $labels             The labels corresponding to the choices in the group.
     * @param array  $preferredChoices   The preferred choices.
     *
     * @throws InvalidConfigurationException If no valid value or index could be created for a choice.
     */
    protected function addChoiceGroup($group, array &$bucketForPreferred, array &$bucketForRemaining, array $choices, array $labels, array $preferredChoices)
    {
        // If this is a choice group, create a new level in the choice
        // key hierarchy
        $bucketForPreferred[$group] = array();
        $bucketForRemaining[$group] = array();

        $this->addChoices(
            $bucketForPreferred[$group],
            $bucketForRemaining[$group],
            $choices,
            $labels,
            $preferredChoices
        );

        // Remove child levels if empty
        if (empty($bucketForPreferred[$group])) {
            unset($bucketForPreferred[$group]);
        }
        if (empty($bucketForRemaining[$group])) {
            unset($bucketForRemaining[$group]);
        }
    }

    /**
     * Adds a new choice.
     *
     * @param array  $bucketForPreferred The bucket where to store the preferred
     *                                   view objects.
     * @param array  $bucketForRemaining The bucket where to store the
     *                                   non-preferred view objects.
     * @param mixed  $choice             The choice to add.
     * @param string $label              The label for the choice.
     * @param array  $preferredChoices   The preferred choices.
     *
     * @throws InvalidConfigurationException If no valid value or index could be created.
     */
    protected function addChoice(array &$bucketForPreferred, array &$bucketForRemaining, $choice, $label, array $preferredChoices)
    {
        $index = $this->createIndex($choice);

        if ('' === $index || null === $index || !FormConfigBuilder::isValidName((string) $index)) {
            throw new InvalidConfigurationException(sprintf('The index "%s" created by the choice list is invalid. It should be a valid, non-empty Form name.', $index));
        }

        $value = $this->createValue($choice);

        if (!is_string($value)) {
            throw new InvalidConfigurationException(sprintf('The value created by the choice list is of type "%s", but should be a string.', gettype($value)));
        }

        $view = new ChoiceView($choice, $value, $label);

        $this->choices[$index] = $this->fixChoice($choice);
        $this->values[$index] = $value;

        if ($this->isPreferred($choice, $preferredChoices)) {
            $bucketForPreferred[$index] = $view;
        } else {
            $bucketForRemaining[$index] = $view;
        }
    }
}
