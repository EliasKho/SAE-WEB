function generateCompositeImage(image1Url, image2Url, containerId) {
    // Créer un élément <canvas> dynamique pour chaque appel
    const canvas = document.createElement("canvas");
    const container = document.getElementById(containerId);
    const context = canvas.getContext("2d");

    // Créer deux objets Image pour charger les images
    const img1 = new Image();
    const img2 = new Image();

    // Charger la première image
    img1.src = image1Url;
    img1.onload = function () {
        // Définir la taille du canvas en fonction de la première image
        canvas.width = img1.width;
        canvas.height = img1.height;

        // Dessiner la première image sur le canvas
        context.drawImage(img1, 0, 0);

        // Charger la deuxième image après la première
        img2.src = image2Url;
        img2.onload = function () {
            // Dessiner la deuxième image avec la même taille et position
            context.drawImage(img2, 0, 0, img1.width, img1.height);

            // Ajouter le canvas avec l'image combinée dans le conteneur
            container.appendChild(canvas);
        };
    };
}
