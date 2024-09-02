<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\Description;
use App\Entity\Entreprise;
use App\Entity\Escape;
use App\Entity\Link;
use App\Entity\ListFavori;
use App\Entity\ListToDo;
use App\Entity\User;
use App\Repository\DescriptionRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\EscapeRepository;
use App\Repository\GradeRepository;
use App\Repository\LinkRepository;
use App\Repository\ListFavoriRepository;
use App\Repository\ListToDoRepository;
use App\Repository\TagRepository;
use Symfony\Component\Serializer\SerializerInterface;

class EscapeService
{
    private DescriptionRepository $descriptionRep;
    private LinkRepository $linkRep;
    private ListToDoRepository $todoRep;
    private ListFavoriRepository $favoriRep;
    private GradeRepository $gradeRep;
    private TagRepository $tagRep;
    private EntrepriseRepository $entrepriseRep;
    private EscapeRepository $escapeRep;
    private SerializerInterface $serializer;

    public function __construct(
        DescriptionRepository $descriptionRep,
        LinkRepository $linkRep,
        ListToDoRepository $todoRep,
        ListFavoriRepository $favoriRep,
        GradeRepository $gradeRep,
        TagRepository $tagRep,
        EntrepriseRepository $entrepriseRep,
        EscapeRepository $escapeRep,
        SerializerInterface $serializer
    ) {
        $this->descriptionRep = $descriptionRep;
        $this->linkRep = $linkRep;
        $this->todoRep = $todoRep;
        $this->favoriRep = $favoriRep;
        $this->gradeRep = $gradeRep;
        $this->tagRep = $tagRep;
        $this->entrepriseRep = $entrepriseRep;
        $this->escapeRep = $escapeRep;
        $this->serializer = $serializer;
    }

    /**
     * Get description by entreprise
     *
     * @param City $city user's city
     * @param Escape $escape escape
     * @param Entreprise $entreprise entreprise of the escape
     *
     * @return Description|null
     */
    public function getDescriptionOfEntreprise(City $city, Escape $escape, Entreprise $entreprise): ?Description
    {
        return $this->descriptionRep->getDescriptionByCityAndEscapeAndEntreprise(
            $city,
            $escape,
            $entreprise
        );
    }

    /**
     * Get first description
     *
     * @param City $city user's city
     * @param Escape $escape escape
     *
     * @return Description|null
     */
    public function getFirstDescription(City $city, Escape $escape): ?Description
    {
        return $this->descriptionRep->getDescriptionByCityAndEscape($city, $escape)[0];
    }

    /**
     * Get link by entreprise
     *
     * @param City $city user's city
     * @param Escape $escape escape
     * @param Entreprise $entreprise entreprise of the escape
     *
     * @return Link|null
     */
    public function getLinkOfEntreprise(City $city, Escape $escape, Entreprise $entreprise): ?Link
    {
        return $this->linkRep->getLinkByCityAndEscapeAndEntreprise(
            $city,
            $escape,
            $entreprise
        );
    }

    /**
     * Get first link
     *
     * @param City $city user's city
     * @param Escape $escape escape
     *
     * @return Link|null
     */
    public function getFirstLink(City $city, Escape $escape): ?Link
    {
        return $this->linkRep->getLinkByCityAndEscape($city, $escape)[0];
    }

    /**
     * Get escape's average and number of votes
     *
     * @param Escape $escape escape
     *
     * @return array<int>
     */
    public function getAverageAndVotes(Escape $escape): array
    {
        $grades = $escape->getGrades();
        $sum = 0;
        $frequency = 0;
        foreach ($grades as $grade) {
            $sum += $grade->getGrade();
            $frequency++;
        }
        if ($frequency != 0) {
            $average = round($sum / $frequency);
        } else {
            $average = 0;
        }

        return ["average" => $average, "votes" => $frequency];
    }

    /**
     * Knowing if escape is in user's to-do list
     *
     * @param User $user user
     * @param Escape $escape escape
     *
     * @return ListToDo|null
     */
    public function knowIfIsToDo(User $user, Escape $escape): ?ListToDo
    {
        return $this->todoRep->isItAlreadyInList($user, $escape);
    }

    /**
     * Knowing if escape is in user's favori list
     *
     * @param User $user user
     * @param Escape $escape escape
     *
     * @return ListFavori|null
     */
    public function knowIfIsFavorite(User $user, Escape $escape): ?ListFavori
    {
        return $this->favoriRep->isItAlreadyInList($user, $escape);
    }

    /**
     * Get User's grade
     *
     * @param User $user user
     * @param Escape $escape escape
     *
     * @return int|null
     */
    public function getUserGrade(User $user, Escape $escape): ?int
    {
        $grade = $this->gradeRep->getGradeByUserAndEscape($user, $escape);

        return $grade ? $grade->getGrade() : null;
    }

    /**
     * Gather escape's informations with entreprise
     *
     * @param User $user current user
     * @param Escape $escape escape to return
     * @param Entreprise $entreprise entreprise of the escape
     *
     * @return string|null
     */
    public function getInformationsWithEntreprise(User $user, Escape $escape, Entreprise $entreprise): ?string
    {
        if ($city = $user->getCity()) {
            // Get right link and description
            $description = $this->descriptionRep->getDescriptionByCityAndEscapeAndEntreprise(
                $city,
                $escape,
                $entreprise
            );
            $link = $this->linkRep->getLinkByCityAndEscapeAndEntreprise(
                $city,
                $escape,
                $entreprise
            );

            // Get average grade
            $grades = $escape->getGrades();
            $sum = 0;
            $frequency = 0;
            foreach ($grades as $grade) {
                $sum += $grade->getGrade();
                $frequency++;
            }
            if ($frequency != 0) {
                $average = round($sum / $frequency);
            } else {
                $average = 0;
            }

            // Get User's list and User's grade informations
            $isToDo = $this->todoRep->isItAlreadyInList($user, $escape) ?? false;
            $isFavorite = $this->favoriRep->isItAlreadyInList($user, $escape) ?? false;
            $userGrade = $this->gradeRep->getGradeByUserAndEscape($user, $escape);

            // Merge data
            $data = array_merge(
                ["escape" => $escape],
                ["description" => $description],
                ["link" => $link],
                ["average" => $average],
                ["votes" => $frequency],
                ["isToDo" => $isToDo],
                ["isFavorite" => $isFavorite],
                ["userGrade" => $userGrade ? $userGrade->getGrade() : null]
            );
            $json = $this->serializer->serialize($data, "json", ["groups" => "getEscape"]);

            return $json;
        }

        return null;
    }

    /**
     * Gather escape's informations
     *
     * @param User $user current user
     * @param Escape $escape escape to return
     *
     * @return string|null
     */
    public function getInformations(User $user, Escape $escape): ?string
    {
        if ($city = $user->getCity()) {
            // Get right link and description
            $description = $this->descriptionRep->getDescriptionByCityAndEscape($city, $escape)[0];
            $link = $this->linkRep->getLinkByCityAndEscape($city, $escape)[0];

            // Get average grade
            $grades = $escape->getGrades();
            $sum = 0;
            $frequency = 0;
            foreach ($grades as $grade) {
                $sum += $grade->getGrade();
                $frequency++;
            }
            if ($frequency != 0) {
                $average = round($sum / $frequency);
            } else {
                $average = 0;
            }

            // Get User's list and User's grade informations
            $isToDo = $this->todoRep->isItAlreadyInList($user, $escape) ?? false;
            $isFavorite = $this->favoriRep->isItAlreadyInList($user, $escape) ?? false;
            $userGrade = $this->gradeRep->getGradeByUserAndEscape($user, $escape);

            // Merge data
            $data = array_merge(
                ["escape" => $escape],
                ["description" => $description],
                ["link" => $link],
                ["average" => $average],
                ["votes" => $frequency],
                ["isToDo" => $isToDo],
                ["isFavorite" => $isFavorite],
                ["userGrade" => $userGrade ? $userGrade->getGrade() : null]
            );
            $json = $this->serializer->serialize($data, "json", ["groups" => "getEscape"]);

            return $json;
        }

        return null;
    }

    /**
     * Find escapes by Entreprises and Tags and city
     *
     * @param City $city current user's city
     * @param array{nbPlayer: int, price: int, level: int, age: int, time: int, actual: bool} $parameters search
     *
     * @return string
     */
    public function findEscapes(City $city, array $parameters): ?string
    {
        // Get all tags and entreprises usefull for current user
        $tags = $this->tagRep->findAll();
        $entreprises = $this->entrepriseRep->getEntreprisesByCity($city);

        // Get escapes by entreprise
        $orderEntreprises = [];
        foreach ($entreprises as $entreprise) {
            if ($orderEntreprises === []) {
                $escapesTmp = $this->escapeRep->getEscapesByEntrepriseAndCity($entreprise, $city, $parameters);
                if (!empty($escapesTmp)) {
                    // check if escape in this city is in this entreprise
                    $escapes = [];
                    foreach ($escapesTmp as $escape) {
                        if ($this->linkRep->getLinkByCityAndEscapeAndEntreprise($city, $escape, $entreprise) !== null) {
                            array_push($escapes, $escape);
                        }
                    }
                    $orderEntreprises = array_merge(["entreprise" => $entreprise], ["escapes" => $escapes]);
                    $orderEntreprises = [$orderEntreprises];
                }
            } else {
                $escapesTmp = $this->escapeRep->getEscapesByEntrepriseAndCity($entreprise, $city, $parameters);
                if (!empty($escapesTmp)) {
                    // check if escape in this city is in this entreprise
                    $escapes = [];
                    foreach ($escapesTmp as $escape) {
                        if ($this->linkRep->getLinkByCityAndEscapeAndEntreprise($city, $escape, $entreprise) !== null) {
                            array_push($escapes, $escape);
                        }
                    }
                    $entrepriseTmp = array_merge(["entreprise" => $entreprise], ["escapes" => $escapes]);
                    $entrepriseTmp = [$entrepriseTmp];
                    $tmp = array_merge($orderEntreprises, $entrepriseTmp);
                    $orderEntreprises = $tmp;
                }
            }
        }

        // Get escapes by tag
        $orderTags = [];
        foreach ($tags as $tag) {
            if ($orderTags === []) {
                $escapeTmp = $this->escapeRep->getEscapesByTagAndCity($tag, $city, $parameters);
                if (!empty($escapeTmp)) {
                    $orderTags = array_merge(["tag" => $tag], ["escapes" => $escapeTmp]);
                    $orderTags = [$orderTags];
                }
            } else {
                $escapeTmp = $this->escapeRep->getEscapesByTagAndCity($tag, $city, $parameters);
                if (!empty($escapeTmp)) {
                    $tagTmp = array_merge(["tag" => $tag], ["escapes" => $escapeTmp]);
                    $tagTmp = [$tagTmp];
                    $tmp = array_merge($orderTags, $tagTmp);
                    $orderTags = $tmp;
                }
            }
        }

        // Merge data
        $data = array_merge(
            ["entreprises" => $orderEntreprises],
            ["tags" => $orderTags]
        );

        $json = $this->serializer->serialize($data, "json", ["groups" => "finder"]);

        return $json;
    }
}
