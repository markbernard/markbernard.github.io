var Invaders = {
    gameId: "",
    keyA: 65,
    keyD: 68,
    keyL: 76,
    context: null,
    canvas: null,
    level: 1,
    score: 0,
    lives: 3,
    playerShip: null,
    playerShipX: 0,
    playerShipY: 0,
    playerBullet: null,
    playerBulletX: 0,
    playerBulletY: 0,
    playerBulletFired: false,
    playerBase: null,
    playerBaseGrid: null,
    playerSpeed: 3,
    left: false,
    right: false,
    resourceCounter: 0,
    alienShip: null,
    alienLeft: 0,
    alienRight: 10,
    alienTop: 0,
    alienX: 0,
    alienY: 0,
    alienDeltaX: 0,
    alienDeltaY: 0,
    alienBottomRow: 4,
    alienCount: 55,
    alienBullet: null,
    alien: [[]],
    alienShotTimer: 0,
    alienShotX: 0,
    alienShotY: 0,
    alienShot: false,
    alienMotherShipX: 0,
    alienMotherShipY: 6,
    alienMotherShipDeltaX: 0,
    alienMotherShipSpeed: 0,
    alienMotherShipCounter: 0,
    alienMotherShipOn: false,
    alienMotherShipScore: 0,
    alienMotherShipScoreCounter: 0,
    mouseX: 0,
    mouseY: 0,
    shotX: 0
};

$(function () {
    "strict mode";
    $("#resetBoard").on("click", function (event) {
        reset();
    });
    reset();
});

function lineIntersectsRectangle(x1, y1, x2, y2, minX, minY, maxX, maxY) {  
    // Completely outside.
    if ((x1 <= minX && x2 <= minX) || (y1 <= minY && y2 <= minY) || (x1 >= maxX && x2 >= maxX) || (y1 >= maxY && y2 >= maxY))
        return false;

    var m = (y2 - y1) / (x2 - x1);

    var y = m * (minX - x1) + y1;
    if (y > minY && y < maxY) return true;

    y = m * (maxX - x1) + y1;
    if (y > minY && y < maxY) return true;

    var x = (minY - y1) / m + x1;
    if (x > minX && x < maxX) return true;

    x = (maxY - y1) / m + x1;
    if (x > minX && x < maxX) return true;

    return false;
}
function reset() {
    "strict mode";
    var i;
    
    Invaders.gameId = guid();
    Invaders.resourceCounter = 0;
    Invaders.canvas = document.getElementById("board");
    Invaders.context = Invaders.canvas.getContext("2d");
    Invaders.level = 1;
    Invaders.score = 0;
    Invaders.lives = 3;
    Invaders.playerShipX = 384;
    Invaders.playerShipY = 555;
    
    Invaders.playerShip = new Image;
    Invaders.resourceCounter += 1;
    Invaders.playerShip.onload = function () {
        Invaders.resourceCounter -= 1;
    }
    Invaders.playerShip.src = "player_ship.png";
    
    Invaders.playerBullet = new Image;
    Invaders.resourceCounter += 1;
    Invaders.playerBullet.onload = function () {
        Invaders.resourceCounter -= 1;
    }
    Invaders.playerBullet.src = "bullet.png";
    
    Invaders.playerBase = [new Image, new Image, new Image];
    Invaders.resourceCounter += 3;
    Invaders.playerBase[0].onload = function () {
        Invaders.resourceCounter -= 1;
    }
    Invaders.playerBase[0].src = "base.png";
    Invaders.playerBase[1].onload = function () {
        Invaders.resourceCounter -= 1;
    }
    Invaders.playerBase[1].src = "baseleft.png";
    Invaders.playerBase[2].onload = function () {
        Invaders.resourceCounter -= 1;
    }
    Invaders.playerBase[2].src = "baseright.png";
    
    //set up player base configuration
    Invaders.playerBaseGrid = new Array;
    for (i=0;i<6;i += 1) {
        Invaders.playerBaseGrid.push(
            [[1, 0, 2],
            [0, 0, 0],
            [0, 0, 0],
            [0, 3, 0]]);
    }
    
    Invaders.alienShip = [new Image, new Image, new Image, new Image];
    Invaders.resourceCounter += 4;
    
    for (i=0;i<4;i+=1) {
        Invaders.alienShip[i].onload = function () {
            Invaders.resourceCounter -= 1;
        }
        Invaders.alienShip[i].src = "invader" + (i + 1) + ".png";
    }
    createAliens(1.25);
    
    Invaders.alienBullet = new Image;
    Invaders.resourceCounter += 1;
    Invaders.alienBullet.onload = function () {
        Invaders.resourceCounter -= 1;
    }
    Invaders.alienBullet.src = "enemybullet1.png";
    
    $("body").on("keydown", function (event) {
        if (Invaders.lives > 0) {
            if (event.which === Invaders.keyA) {
                Invaders.left = true;
            } else if (event.which === Invaders.keyD) {
                Invaders.right = true;
            } else if (event.which === Invaders.keyL) {
                if (Invaders.playerBulletFired === false) {
                    Invaders.playerBulletFired = true;
                    Invaders.playerBulletX = Invaders.playerShipX + 11;
                    Invaders.playerBulletY = Invaders.playerShipY + 8;
                }
            }
        }
    });
    $("body").on("keyup", function (event) {
        if (event.which === Invaders.keyA) {
            Invaders.left = false;
        } else if (event.which === Invaders.keyD) {
            Invaders.right = false;
        }
    });
    $("#board").on("mousemove", function (event) {
        Invaders.mouseX = event.pageX - 256;
        Invaders.mouseY = event.pageY - 216;
    });
    setTimeout(function () {
        cycle(Invaders.gameId);
    }, 30);
}

function createAliens(speed) {
    "strict mode";
    Invaders.alien = [
        [true, true, true, true, true], 
        [true, true, true, true, true], 
        [true, true, true, true, true], 
        [true, true, true, true, true], 
        [true, true, true, true, true], 
        [true, true, true, true, true], 
        [true, true, true, true, true], 
        [true, true, true, true, true], 
        [true, true, true, true, true], 
        [true, true, true, true, true], 
        [true, true, true, true, true], 
        ];
    Invaders.alienLeft = 0;
    Invaders.alienRight = 10;
    Invaders.alienTop = 0;
    Invaders.alienX = 0;
    Invaders.alienY = 25;
    Invaders.alienBottomRow = 4;
    Invaders.alienDeltaX = speed;
    Invaders.alienDeltaY = 10;
    Invaders.alienCount = 55;
    Invaders.alienMotherShipSpeed = speed * 2;
    Invaders.alienMotherShipCounter = 500;
}

function update() {
    "strict mode";
    var collisionX, collisionY, i, advanceColumn = true, shotX, shotY;
    if (Invaders.left === true) {
        Invaders.playerShipX -= Invaders.playerSpeed;
        if (Invaders.playerShipX <= 0) {
            Invaders.playerShipX = 0;
        }
    } else if (Invaders.right === true) {
        Invaders.playerShipX += Invaders.playerSpeed;
        if (Invaders.playerShipX >= 768) {
            Invaders.playerShipX = 768;
        }
    }
    if (Invaders.playerBulletFired === true) {
        var shotColumn = (Invaders.playerBulletX - 101) / 110,
            shotRow, 
            baseColumn;
        if (shotColumn >= 0 && shotColumn < 6) {
            shotColumn = Math.floor(shotColumn);
            baseColumn = Math.floor(((Invaders.playerBulletX - 101) - (shotColumn * 110)) / 16);
            for (shotRow=0;shotRow<4;shotRow += 1) {
                if (Invaders.playerBaseGrid[shotColumn][shotRow][baseColumn] !== 3 && 
                        baseColumn >= 0 && baseColumn < 3 &&
                        lineIntersectsRectangle(
                            Invaders.playerBulletX, Invaders.playerBulletY, 
                            Invaders.playerBulletX, Invaders.playerBulletY + 10, 
                            (shotColumn * 110 + 101) + baseColumn * 16, 516 + (shotRow * 7), 
                            (shotColumn * 110 + 117) + baseColumn * 16, 516 + (shotRow * 7) + 7)) {
                    Invaders.playerBulletFired = false;
                    Invaders.playerBaseGrid[shotColumn][shotRow][baseColumn] = 3;
                }
            }
        }
    }
    if (Invaders.playerBulletFired === true) {
        collisionX = Math.floor((Invaders.playerBulletX - Invaders.alienX) / 48);
        collisionY = Math.floor((Invaders.playerBulletY - Invaders.alienY) / 48);
        if (collisionX >= 0 && collisionX <= 10 && collisionY >= 0 && collisionY <= 4 
                && Invaders.alien[collisionX][collisionY] === true) {
            Invaders.alien[collisionX][collisionY] = false;
            Invaders.playerBulletFired = false;
            Invaders.alienCount -= 1;
            Invaders.score += (50 + 50 * Math.floor(((6 - collisionY) / 2)) + (50 * Invaders.level));
            if (Invaders.alienCount <= 0) {
                Invaders.level += 1;
                createAliens(1 + (Invaders.level * 0.25));
            } else {
                while (advanceColumn === true && Invaders.alienLeft !== Invaders.alienRight) {
                    for (i=0;i<11;i += 1) {
                        if (Invaders.alien[Invaders.alienLeft][i] === true) {
                            advanceColumn = false;
                            break;
                        }
                    }
                    if (advanceColumn === true) {
                        Invaders.alienLeft += 1;
                    }
                }
                advanceColumn = true;
                while (advanceColumn === true && Invaders.alienLeft !== Invaders.alienRight) {
                    for (i=0;i<11;i += 1) {
                        if (Invaders.alien[Invaders.alienRight][i] === true) {
                            advanceColumn = false;
                            break;
                        }
                    }
                    if (advanceColumn === true) {
                        Invaders.alienRight -= 1;
                    }
                }
            }
        }
        
        if (Invaders.alienMotherShipScoreCounter > 0) {
            Invaders.alienMotherShipScoreCounter -= 1;
        }
        if (Invaders.playerBulletX >= Invaders.alienMotherShipX && 
                Invaders.playerBulletX <= Invaders.alienMotherShipX + 48 &&
                Invaders.playerBulletY >= Invaders.alienMotherShipY + 20 &&
                Invaders.playerBulletY <= Invaders.alienMotherShipY + 34) {
            Invaders.alienMotherShipOn = false;
            Invaders.playerBulletFired = false;
            Invaders.alienMotherShipScore = ((Math.floor(Math.random() * 9) + 2) * 50) + (Invaders.level * 100) + 100;
            Invaders.alienMotherShipScoreCounter = 30;
            Invaders.score += Invaders.alienMotherShipScore;
            if (Invaders.alienMotherShipX < 5) {
                Invaders.alienMotherShipX = 5;
            }
            if (Invaders.alienMotherShipX > 747) {
                Invaders.alienMotherShipX = 747;
            }
        }
        
        Invaders.playerBulletY -= 10;
        if (Invaders.playerBulletY <= -10) {
            Invaders.playerBulletFired = false;
        }
    }
    Invaders.alienX += Invaders.alienDeltaX;
    if (Invaders.alienX <= 0 - (Invaders.alienLeft * 48) || Invaders.alienX >= (800 - ((Invaders.alienRight + 1) * 48))) {
        Invaders.alienDeltaX *= -1;
        Invaders.alienY += Invaders.alienDeltaY;
    }
    if (Invaders.alienY + 48 + (Invaders.alienBottomRow * 48) >= 600) {
        Invaders.lives = 0;
        Invaders.alienDeltaX = 0;
        Invaders.alienDeltaY = 0;
    }

    if (Invaders.alienShot === false) {
        Invaders.alienShotTimer -= 1;
    } else {
        Invaders.alienShotY += 5;
        if (Invaders.alienShotX >= (Invaders.playerShipX + 3) && Invaders.alienShotX <= (Invaders.playerShipX + 28) &&
                Invaders.alienShotY >= (Invaders.playerShipY + 9) && Invaders.alienShotY <= (Invaders.playerShipY + 29) &&
                Invaders.lives > 0) {
            Invaders.alienShot = false;
            Invaders.lives -= 1;
        } else {
            var shotColumn = (Invaders.alienShotX - 101) / 110,
                shotRow = (Invaders.alienShotY - 516) / 7, 
                baseColumn;
            if (shotRow >= 0 && shotRow < 4 && shotColumn >= 0 && shotColumn < 6) {
                shotRow = Math.floor(shotRow);
                shotColumn = Math.floor(shotColumn);
                baseColumn = Math.floor(((Invaders.alienShotX - 101) - (shotColumn * 110)) / 16);
                if (baseColumn >= 0 && baseColumn < 3 && Invaders.playerBaseGrid[shotColumn][shotRow][baseColumn] !== 3) {
                    Invaders.alienShot = false;
                    Invaders.playerBaseGrid[shotColumn][shotRow][baseColumn] = 3;
                }
            }
        }
    }
    if (Invaders.alienShotTimer <= 0) {
        Invaders.alienShotTimer = (Math.random() * 100);
        Invaders.shotX = Math.floor((Invaders.playerShipX - Invaders.alienX) / 48);
        if (Invaders.shotX < 0 || Invaders.shotX > 10) {
            Invaders.alienShotTimer = 10;
        } else {
            shotY = Math.floor(Math.random() * 5);
            while (Invaders.alien[Invaders.shotX][shotY] === false) {
                Invaders.shotX += 1;
                if (Invaders.shotX > 10) {
                    Invaders.shotX = 0;
                    shotY += 1;
                    if (shotY > 4) {
                        shotY = 0;
                    }
                }
            }
            Invaders.alienShotX = Invaders.shotX * 48 + Invaders.alienX + 20;
            Invaders.alienShotY = shotY * 48 + Invaders.alienY + 36;
            Invaders.alienShot = true;
        }
    }
    if (Invaders.alienShotY > 600) {
        Invaders.alienShot = false;
    }
    
    if (Invaders.alienMotherShipCounter <= 0 && Invaders.alienMotherShipScoreCounter <= 0) {
        Invaders.alienMotherShipCounter = 500;
        Invaders.alienMotherShipOn = true;
        if (Math.random() > 0.5) {
            Invaders.alienMotherShipX = -48;
            Invaders.alienMotherShipDeltaX = Invaders.alienMotherShipSpeed;
        } else {
            Invaders.alienMotherShipX = 800;
            Invaders.alienMotherShipDeltaX = -Invaders.alienMotherShipSpeed;
        }
    } else {
        Invaders.alienMotherShipCounter -= 1;
    }
    
    if (Invaders.alienMotherShipOn === true) {
        Invaders.alienMotherShipX += Invaders.alienMotherShipDeltaX;
        if (Invaders.alienMotherShipX < -48 || Invaders.alienMotherShipX > 800) {
            Invaders.alienMotherShipOn = false;
        }
    }
}

function render() {
    "strict mode";
    var x, y, row, col;
    
    Invaders.context.fillStyle = "black";
    Invaders.context.fillRect(0, 0, 800, 600);
    Invaders.context.stroke();
    if (Invaders.lives > 0) {
        Invaders.context.drawImage(Invaders.playerShip, Invaders.playerShipX, Invaders.playerShipY);
    }
    if (Invaders.playerBulletFired === true) {
        Invaders.context.drawImage(Invaders.playerBullet, Invaders.playerBulletX, Invaders.playerBulletY);
    }
    for (x=0;x<11;x += 1) {
        for (y=0;y<5;y += 1) {
            if (Invaders.alien[x][y]) {
                Invaders.context.drawImage(Invaders.alienShip[Math.floor((y + 1) / 2)], x * 48 + Invaders.alienX, y * 48 + Invaders.alienY);
            }
        }
    }
    if (Invaders.alienMotherShipOn === true) {
        Invaders.context.drawImage(Invaders.alienShip[3], Invaders.alienMotherShipX, Invaders.alienMotherShipY);
    }
    
    if (Invaders.alienShot === true) {
        Invaders.context.drawImage(Invaders.alienBullet, Invaders.alienShotX, Invaders.alienShotY);
    }
    for (x=0;x<6;x += 1) {
        for (col=0;col<3;col += 1) {
            for (row=0;row<4;row += 1) {
                if (Invaders.playerBaseGrid[x][row][col] !== 3) {
                    Invaders.context.drawImage(Invaders.playerBase[Invaders.playerBaseGrid[x][row][col]], x * 110 + 101 + (col * 16), 516 + (row * 7));
                }
            }
        }
    }
    
    Invaders.context.fillStyle = "white";
    Invaders.context.font = "bold 24px Courier New";
    Invaders.context.fillText("Score: " + Invaders.score, 10, 20);
    Invaders.context.fillText("Level: " + Invaders.level, 400, 20);
    Invaders.context.fillText("Lives: " + Invaders.lives, 650, 20);
    if (Invaders.alienMotherShipOn === false && Invaders.alienMotherShipScoreCounter > 0) {
        Invaders.context.fillText("" + Invaders.alienMotherShipScore, Invaders.alienMotherShipX + 10, Invaders.alienMotherShipY + 40);
    }
    
    if (Invaders.lives <= 0) {
        Invaders.context.fillText("GAME OVER!", 350, 300);
    }

    Invaders.context.beginPath();
    for (x=0;x<6;x += 1) {
        Invaders.context.moveTo(x * 110 + 98, 514);
        Invaders.context.lineTo(x * 110 + 149, 514);
        Invaders.context.lineTo(x * 110 + 149, 543);
        Invaders.context.lineTo(x * 110 + 98, 543);
        Invaders.context.lineTo(x * 110 + 98, 514);
    }
}

function cycle(cycleGameId) {
    if (Invaders.resourceCounter <= 0) {
        update();
        render();
    }
    if (Invaders.gameId === cycleGameId) {
        setTimeout(function () {
            cycle(cycleGameId);
        }, 30);
    }
}

function guid() {
    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
            .toString(16)
            .substring(1);
    }
    return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
        s4() + '-' + s4() + s4() + s4();
}
