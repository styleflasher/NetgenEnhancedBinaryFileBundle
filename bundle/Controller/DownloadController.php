<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Controller;

use DOMDocument;
use DOMXPath;
use Ibexa\Bundle\Core\Controller;
use Ibexa\Bundle\IO\BinaryStreamResponse;
use Ibexa\Core\Base\Exceptions\InvalidArgumentValue;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\IO\IOServiceInterface;
use Ibexa\Core\Repository\SiteAccessAware\Repository;
use InvalidArgumentException;
use Netgen\InformationCollection\Doctrine\Entity\EzInfoCollection;
use Netgen\InformationCollection\Doctrine\Entity\EzInfoCollectionAttribute;
use Netgen\InformationCollection\Doctrine\Repository\EzInfoCollectionAttributeRepository;
use Netgen\InformationCollection\Doctrine\Repository\EzInfoCollectionRepository;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DownloadController extends Controller
{
    private $infocollectionAttributeRepository;
    private $infocollectionRepository;
    private $ioService;
    private $repository;

    public function __construct(
        EzInfoCollectionAttributeRepository $infocollectionAttributeRepository,
        EzInfoCollectionRepository          $infocollectionRepository,
        IOServiceInterface                  $ioService,
        Repository                          $repository
    )
    {
        $this->infocollectionAttributeRepository = $infocollectionAttributeRepository;
        $this->infocollectionRepository = $infocollectionRepository;
        $this->ioService = $ioService;
        $this->repository = $repository;
    }

    /**
     * @param int $infocollectionAttributeId
     *
     * @return BinaryStreamResponse
     * @throws NotFoundException
     * @throws InvalidArgumentValue
     */
    #[Route(name: 'netgen_enhancedezbinaryfile.route.download_binary_file', path: '/netgen/enhancedezbinaryfile/download/{infocollectionAttributeId}', methods: ['GET'])]
    public function downloadCollectedEnhancedEzBinaryFileAction($infocollectionAttributeId)
    {
        /** @var EzInfoCollectionAttribute|null $infocollectionAttribute */
        $infocollectionAttribute = $this->infocollectionAttributeRepository->find($infocollectionAttributeId);

        if ($infocollectionAttribute === null) {
            throw new NotFoundException(
                "EzInfoCollectionAttribute",
                $infocollectionAttributeId
            );
        }

        /** @var EzInfoCollection|null $infocollection */
        $infocollection = $this->infocollectionRepository->find($infocollectionAttribute->getInformationCollectionId());

        if ($infocollection === null) {
            throw new NotFoundException(
                "EzInfoCollection",
                $infocollectionAttribute->getInformationCollectionId()
            );
        }

        $contentId = $infocollection->getContentObjectId();
        $content = $this->repository->getContentService()->loadContent($contentId);

        if (!$this->repository->getPermissionResolver()->canUser('infocollector', 'read', $content)) {
            throw new AccessDeniedException('Access denied.');
        }

        $binaryFileXML = $infocollectionAttribute->getDataText();
        if (empty($binaryFileXML)) {
            throw new NotFoundException(
                "Binary file info in EzInfoCollectionAttribute",
                $infocollectionAttributeId
            );
        }

        $doc = new DOMDocument('1.0', 'utf-8');
        if (!@$doc->loadXML($binaryFileXML)) {
            throw new InvalidArgumentException(
                "Invalid XML in information collection attribute #{$infocollectionAttributeId}"
            );
        }

        $xpath = new DOMXPath($doc);
        $filePathNodes = $xpath->evaluate('/binaryfile-info/binaryfile-attributes/Filename');
        $originalFilenameNodes = $xpath->evaluate('/binaryfile-info/binaryfile-attributes/OriginalFilename');

        if (!$filePathNodes || $filePathNodes->length === 0) {
            throw new NotFoundException(
                "Filename in XML of EzInfoCollectionAttribute",
                $infocollectionAttributeId
            );
        }

        $filePath = $filePathNodes->item(0)->textContent;
        $fileName = basename($filePath);

        $originalFilename = html_entity_decode($originalFilenameNodes && $originalFilenameNodes->length ? $originalFilenameNodes->item(0)->textContent : $fileName);

        $this->ioService->setPrefix(null);
        $binaryFile = $this->ioService->loadBinaryFile('original' . \DIRECTORY_SEPARATOR . 'collected' . \DIRECTORY_SEPARATOR . $fileName);

        $response = new BinaryStreamResponse($binaryFile, $this->ioService);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $originalFilename);

        return $response;
    }
}
