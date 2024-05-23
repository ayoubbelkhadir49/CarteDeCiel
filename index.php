<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Carte Céleste Dynamique</title>
<style>
    /* Style pour la carte céleste */
    body {
        background-color: rgba(1, 1, 10, 0.8);
        overflow: hidden;
        background-image: url("images/univ.gif");
    }
    #container {
        width: 100vw;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    #celestial-map {
        position: relative;
        width: 600px;
        height: 600px;
        border: 2px solid white;
        border-radius: 50%;
        overflow: hidden;
        background-image: url("images/ciel.jpg");
        background-position: center;
        background-size: cover;
        cursor: grab;
    }
    .star {
        position: absolute;
        width: 10px;
        height: 10px;
        background-color: white;
        border-radius: 50%;
        border: 1px solid yellow;
        cursor: pointer;
        transition: transform 0.3s;
    }
    .star.hover {
    background-color: yellow; /* Choisissez la couleur de survol que vous préférez */
    border-color: white; /* Couleur de la bordure */
}
    .planet {
        position: absolute;
        width: 20px;
        height: 20px;
        background-size: cover;

        cursor: pointer;
        transition: transform 0.3s;
    }
    .popup {
        position: absolute;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 5px;
        border-radius: 5px;
        display: none;
    }
    #planet-index {
        position: absolute;
        right: 0;
        top: 0;
        width: 200px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 10px;
    }
    #planet-index img {
        width: 20px;
        height: 20px;
    }

    #options-form {
    position: absolute;
    top: 20px;
    left: 20px;
    z-index: 9999; /* Assure que l'en-tête est au-dessus des autres éléments */
    color: white; /* Changer la couleur du texte pour une meilleure visibilité */
    }
    .constellation-line {
        position: absolute;
    }
</style>
</head>
<body>
<div id="options-form">
    <form id="options-form" method="post">
    <h3>Options de Visualisation</h3>
        <select id="options-select" name="options-select">
            <option value="closest">Les étoiles les plus proches</option>
            <option value="largest">Les étoiles les plus grosses</option>
            <option value="hottest">Les étoiles les plus chaudes</option>
            <option value="brightest">Les étoiles les plus brillantes</option>
            <option value="all">Toutes les étoiles</option>
        </select>
        <input type="submit" value="Sélectionner">
    </form>
</div>
<br>
<div id="container">
    <div id="celestial-map">
        <?php
        // Connexion à la base de données
        include "includes/bd.php";

        // Liste des noms de constellations
        $constellationNames = [
            "Cas" => "Cassiopée",
            "Psc" => "Poissons",
            "Peg" => "Pégase",
            "Scl" => "Sculpteur",
            "Cet" => "Baleine",
            "Cep" => "Céphée",
            "And" => "Andromède",
            "Phe" => "Phénix",
            "Tri" => "Triangle",
            "Per" => "Persée",
            "Ari" => "Bélier",
            "For" => "Fourneau",
            "UMi" => "Petit Chien",
            "Eri" => "Éridan",
            "Cam" => "Girafe",
            "Tau" => "Taureau",
            "Hor" => "Horloge",
            "Cae" => "Burin",
            "Ori" => "Orion",
            "Aur" => "Coche",
            "Lep" => "Lièvre",
            "Col" => "Colombe",
            "Pic" => "Pic",
            "Mon" => "Licorne",
            "Gem" => "Gémeaux",
            "CMa" => "Grand Chien",
            "Lyn" => "Lynx",
            "Pup" => "Poupe",
            "CMi" => "Petit Chien",
            "Cnc" => "Cancer",
            "Vel" => "Voilier",
            "Hya" => "Hydre Mâle",
            "UMa" => "Grande Ourse",
            "Pyx" => "Boussole",
            "Leo" => "Lion",
            "Ant" => "Machine Pneumatique",
            "LMi" => "Petit Lion",
            "Dra" => "Dragon",
            "Sex" => "Sextant",
            "Crt" => "Coupe",
            "Cen" => "Centaure",
            "Vir" => "Vierge",
            "Com" => "Chevelure de Bérénice",
            "Crv" => "Corbeau",
            "CVn" => "Chiens de Chasse",
            "Boo" => "Bouvier",
            "Lup" => "Loup",
            "Lib" => "Balance",
            "Ser" => "Serpentaire",
            "CrB" => "Couronne Boréale",
            "Sco" => "Scorpion",
            "Her" => "Hercule",
            "Oph" => "Ophiuchus",
            "Nor" => "Règle",
            "Sgr" => "Sagittaire",
            "CrA" => "Couronne Australe",
            "Lyr" => "Lyre",
            "Sct" => "Sculpteur",
            "Aql" => "Aigle",
            "Vul" => "Petit Renard",
            "Cyg" => "Cygne",
            "Sge" => "Flèche",
            "Cap" => "Capricorne",
            "Del" => "Dauphin",
            "Mic" => "Microscope",
            "Aqr" => "Verseau",
            "Equ" => "Écu de Sobieski",
            "Gru" => "Grue",
            "PsA" => "Poisson Australe",
            "Lac" => "Lézard"
        ];

        // Fonction pour exécuter une requête SQL et récupérer les résultats sous forme de tableau associatif
        function executeQuery($pdo, $query) {
            $statement = $pdo->query($query);
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        // Fonction pour créer une étoile ou une planète sur la carte
        function createCelestialObject($id, $x, $y, $constellationCode, $name, $type = 'star', $image = '') {
            global $constellationNames;
            $constellation = isset($constellationNames[$constellationCode]) ? $constellationNames[$constellationCode] : $constellationCode;
            if ($type == 'planet') {
                echo '<div class="planet" id="planet-' . $id . '" style="left:' . ($x + 300 - 10) . 'px; top:' . ($y + 300 - 10) . 'px; background-image: url(' . $image . ');" title="' . $name . '"></div>';
            } else {
                echo '<div class="star" id="star-' . $id . '" style="left:' . ($x + 300 - 5) . 'px; top:' . ($y + 300 - 5) . 'px;" data-constellation="' . $constellation .'" title="' . $name . ' "></div>';
            }
        }

        // Fonction pour récupérer les étoiles en fonction de l'option sélectionnée
        function getStarsByOption($pdo, $option) {
            switch($option) {
                case 'closest':
                    return executeQuery($pdo, "SELECT * FROM tabciel WHERE mag < 6 ORDER BY dist ASC LIMIT 50");
                case 'largest':
                    return executeQuery($pdo, "SELECT * FROM tabciel ORDER BY lum DESC LIMIT 50");
                case 'hottest':
                    return executeQuery($pdo, "SELECT * FROM tabciel ORDER BY spect DESC LIMIT 50");
                case 'brightest':
                    return executeQuery($pdo, "SELECT * FROM tabciel ORDER BY mag ASC LIMIT 50");
                case 'all':
                default:
                    return executeQuery($pdo, "SELECT * FROM tabciel");
            }
        }

        // Récupérer l'option sélectionnée
        $selectedOption = isset($_POST['options-select']) ? $_POST['options-select'] : 'all';

        // Récupérer les étoiles en fonction de l'option sélectionnée
        $stars = getStarsByOption($pdo, $selectedOption);

        // Ajouter les étoiles à la carte
        foreach ($stars as $star) {
            createCelestialObject($star['id'], $star['x'], $star['y'], $star['con'], $star['proper']);
        }

        // Ajouter les planètes à la carte
        $planets = [
            ['id' => 1, 'x' => 0, 'y' => 0, 'name' => 'Sun', 'image' => 'images/sun.png'],
            ['id' => 2, 'x' => 50, 'y' => 55, 'name' => 'Moon', 'image' => 'images/moon.png'],
            ['id' => 3, 'x' => 100, 'y' => 150, 'name' => 'Mercure', 'image' => 'images/mercury.png'],
            ['id' => 4, 'x' => 200, 'y' => 250, 'name' => 'Vénus', 'image' => 'images/venuse.png'],
            ['id' => 5, 'x' => 300, 'y' => 350, 'name' => 'Terre', 'image' => 'images/terre.png'],
            ['id' => 6, 'x' => 400, 'y' => 450, 'name' => 'Mars', 'image' => 'images/mars.png'],
            ['id' => 7, 'x' => 500, 'y' => 550, 'name' => 'Jupiter', 'image' => 'images/jupiter.png'],
            ['id' => 8, 'x' => 600, 'y' => 650, 'name' => 'Saturne', 'image' => 'images/saturne.png'],
            ['id' => 9, 'x' => 700, 'y' => 750, 'name' => 'Uranus', 'image' => 'images/uranus.png'],
            ['id' => 10, 'x' => 800, 'y' => 850, 'name' => 'Neptune', 'image' => 'images/neptune.png'],
        ];

        foreach ($planets as $planet) {
            createCelestialObject($planet['id'], $planet['x'], $planet['y'], '', $planet['name'], 'planet', $planet['image']);
        }
        ?>
    </div>

    <div id="planet-index">
        <h3>Index des Planètes</h3>
        <ul>
        <li><img src="images/sun.png" alt="Sun"> Sun</li>
        <li><img src="images/moon.png" alt="Moon"> Moon</li>
        <li><img src="images/mercury.png" alt="Mercure"> Mercure</li>
        <li><img src="images/venuse.png" alt="Vénus"> Vénus</li>
        <li><img src="images/terre.png" alt="Terre"> Terre</li>
        <li><img src="images/mars.png" alt="Mars"> Mars</li>
        <li><img src="images/jupiter.png" alt="Jupiter"> Jupiter</li>
        <li><img src="images/saturne.png" alt="Saturne"> Saturne</li>
        <li><img src="images/uranus.png" alt="Uranus"> Uranus</li>
        <li><img src="images/neptune.png" alt="Neptune"> Neptune</li>
        </ul>
    </div>
</div>

<div id="star-popup" class="popup"></div>

<script>
    // Gestion des événements pour le survol des étoiles de la même constellation
    document.querySelectorAll('.star').forEach(star => {
    star.addEventListener('mouseover', event => {
        const constellation = event.target.getAttribute('data-constellation');
        const starsInConstellation = document.querySelectorAll(`.star[data-constellation="${constellation}"]`);
        starsInConstellation.forEach(star => {
            star.classList.add('hover');
        });
    });

    star.addEventListener('mouseout', event => {
        const constellation = event.target.getAttribute('data-constellation');
        const starsInConstellation = document.querySelectorAll(`.star[data-constellation="${constellation}"]`);
        starsInConstellation.forEach(star => {
            star.classList.remove('hover');
        });
    });
});
    // Gestion des événements pour afficher les infos des étoiles et des planètes
    document.querySelectorAll('.star, .planet').forEach(celestialObject => {
        celestialObject.addEventListener('mouseover', event => {
            const popup = document.getElementById('star-popup');
            const starName = event.target.getAttribute('title'); // Récupérer le nom de l'étoile
            const constellation = event.target.getAttribute('data-constellation');
            popup.innerHTML = '<strong> Nom de étoile : ' + starName + '</strong><br> Nom de constellation :' + constellation;
            
            popup.style.left = event.pageX + 'px';
            popup.style.top = event.pageY + 'px';
            popup.style.display = 'block';
        });
        
        celestialObject.addEventListener('mouseout', () => {
            const popup = document.getElementById('star-popup');
            popup.style.display = 'none';
        });
    });

    // Ajout de la possibilité de déplacer la carte céleste
    const celestialMap = document.getElementById('celestial-map');
    let isDragging = false;
    let startX, startY, initialLeft, initialTop;

    celestialMap.addEventListener('mousedown', (e) => {
        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        initialLeft = celestialMap.scrollLeft;
        initialTop = celestialMap.scrollTop;
        celestialMap.style.cursor = 'grabbing';
    });

    document.addEventListener('mouseup', () => {
        isDragging = false;
        celestialMap.style.cursor = 'grab';
    });

    document.addEventListener('mousemove', (e) => {
        if (isDragging) {
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            celestialMap.scrollLeft = initialLeft - dx;
            celestialMap.scrollTop = initialTop - dy;
        }
    });

// Fonction pour dessiner les lignes reliant les étoiles de la même constellation
function drawConstellationLines() {
    const stars = document.querySelectorAll('.star');
    const linesContainer = document.getElementById('celestial-map');

    stars.forEach(star => {
        const constellation = star.getAttribute('data-constellation');
        const starsInConstellation = document.querySelectorAll(`.star[data-constellation="${constellation}"]`);
        if (starsInConstellation.length > 1) {
            starsInConstellation.forEach(otherStar => {
                if (otherStar !== star) {
                    const startX = parseInt(star.style.left) + 5; // Ajoutez la moitié de la taille de l'étoile pour centrer la ligne
                    const startY = parseInt(star.style.top) + 5;
                    const endX = parseInt(otherStar.style.left) + 5;
                    const endY = parseInt(otherStar.style.top) + 5;

                    const line = document.createElement('div');
                    line.classList.add('constellation-line');
                    line.style.position = 'absolute';
                    line.style.width = Math.sqrt((endX - startX) ** 2 + (endY - startY) ** 2) + 'px';
                    line.style.height = '1px';
                    line.style.backgroundColor = 'white';
                    line.style.left = startX + 'px';
                    line.style.top = startY + 'px';
                    line.style.transformOrigin = '0 0';
                    line.style.transform = 'rotate(' + Math.atan2(endY - startY, endX - startX) + 'rad)';
                    linesContainer.appendChild(line);
                }
            });
        }
    });
}

    // Appelez la fonction pour dessiner les lignes des constellations
    drawConstellationLines();
</script>
</body>
</html>
