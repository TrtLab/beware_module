<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use FPDF;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    /**
     * @Route("/products", name="products")
     */
    public function index()
    {
        $products = $this->getDoctrine()->getRepository(Product::class)->findAll();

        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'products' => $products
        ]);
    }

    /**
     * @Route("/product/{id}/edit", name="product_edit", methods={"GET", "POST"})
     */
    public function edit(Product $product, Request $request, Swift_Mailer $mailer)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            // Sends an email if product's quantity goes to 0
            if($product->getQuantity() == 0){
                $message = (new \Swift_Message('Rupture de stock sur un matériel'))
                ->setFrom('noreply@bewaremodule.com')
                ->setTo('admin@bewaremodule.com')
                ->setBody('Bonjour, le matériel suivant : ' . $product->getName() . ' est en rupture de stock. Pensez à vous réapprovisionner.');

                $mailer->send($message);
            }
         
            $this->addFlash('notice', 'Le matériel a été correctement modifié.');
            return $this->redirectToRoute('products');
        }

        return $this->render('product/productModal.html.twig', [
            'controller_name' => 'ProductController',
            'form' => $form->createView(),
            'product' => $product
        ]);
    }

    /**
     * @Route("/product/{id}/pdf", name="product_pdf")
     */
    public function pdf(Product $product)
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(40,10,utf8_decode('Nom du matériel: ' . $product->getName()));
        $pdf->Ln();
        $pdf->Cell(40,10,utf8_decode('Prix: ' . $product->getPrice() . ' euros'));
        $pdf->Ln();
        $pdf->Cell(40,10,utf8_decode('Quantité restante: ' . $product->getQuantity() . ' pièces.'));
        $pdf->Ln();
        $pdf->Cell(40,10,utf8_decode('Date de création: ' . $product->getCreatedAt()->format('d/m/Y')));
        
        return new Response($pdf->Output(), 200, ['Content-Type' => 'application/pdf']);
    }
}
