var TicTacToe = {
    context: null,
    canvas: null,
    running: true,
    count: 0,
    x: 0,
    y: 0,
    boardX: -1,
    boardY: -1,
    player: -1,
    board: [[0, 0, 0], [0, 0, 0], [0, 0, 0]],
    names: ["Xs", "", "Os"]
};

$(function () {
    "strict mode";
    TicTacToe.x = Math.round($("#board").offset().left);
    TicTacToe.y = Math.round($("#board").offset().top);
    TicTacToe.canvas = document.getElementById("board");
    TicTacToe.context = TicTacToe.canvas.getContext("2d");
    reset();
    
    $("#board").on("click", function (data) {
        if (TicTacToe.running === true) {
            calculateBox(data.pageX - TicTacToe.x, data.pageY - TicTacToe.y) - 1;
            if (TicTacToe.boardX != -1 && TicTacToe.boardY != -1) {
                if (TicTacToe.board[TicTacToe.boardX][TicTacToe.boardY] == 0) {
                    TicTacToe.count += 1;
                    TicTacToe.board[TicTacToe.boardX][TicTacToe.boardY] = TicTacToe.player;
                    if (TicTacToe.player == 1) {
                        drawO(TicTacToe.boardX, TicTacToe.boardY);
                    }
                    else if (TicTacToe.player == -1) {
                        drawX(TicTacToe.boardX, TicTacToe.boardY);
                    }
                    if (checkForWin(TicTacToe.boardX, TicTacToe.boardY, TicTacToe.player)) {
                        TicTacToe.running = false;
                        drawWinLine(TicTacToe.boardX, TicTacToe.boardY, TicTacToe.player);
                        alert(TicTacToe.names[TicTacToe.player + 1] + " won the game");
                    }
                    TicTacToe.player = TicTacToe.player * -1;
                    if (TicTacToe.count >= 9 && TicTacToe.running === true) {
                        TicTacToe.running = false;
                        alert("The game ended in a draw.");
                    }
                } else {
                    alert("Square is taken");
                }
            }
        } else {
            alert("The game is over");
        }
    });
    
    $("#resetBoard").on("click", function (data) {
        reset();
    });
});

function reset() {
    "strict mode";
    TicTacToe.running = true;
    TicTacToe.board = [[0, 0, 0], [0, 0, 0], [0, 0, 0]];
    TicTacToe.count = 0;
    TicTacToe.boardX = -1;
    TicTacToe.boardY = -1;
    TicTacToe.player = -1;
    TicTacToe.context.beginPath();
    TicTacToe.context.fillStyle = "black";
    TicTacToe.context.moveTo(0, 0);
    TicTacToe.context.clearRect(0, 0, 400, 400);
    TicTacToe.context.moveTo(150, 50);
    TicTacToe.context.lineTo(150, 350);
    TicTacToe.context.moveTo(250, 50);
    TicTacToe.context.lineTo(250, 350);
    TicTacToe.context.moveTo(50, 150);
    TicTacToe.context.lineTo(350, 150);
    TicTacToe.context.moveTo(50, 250);
    TicTacToe.context.lineTo(350, 250);
    TicTacToe.context.strokeStyle = "white";
    TicTacToe.context.lineWidth = 3;
    TicTacToe.context.stroke();
}
function calculateBox(x, y) {
    "strict mode";
    TicTacToe.boardX = -1;
    TicTacToe.boardY = -1;
    if (x > 50 && x < 350 && y > 50 && y < 350) {
        TicTacToe.boardX = Math.floor((x - 50) / 100);
        TicTacToe.boardY = Math.floor((y - 50) / 100);
    }
}

function drawX(x, y) {
    "strict mode";
    TicTacToe.context.beginPath();
    TicTacToe.context.moveTo((x * 100) + 70, (y * 100) + 70);
    TicTacToe.context.lineTo((x * 100) + 130, (y * 100) + 130);
    TicTacToe.context.moveTo((x * 100) + 70, (y * 100) + 130);
    TicTacToe.context.lineTo((x * 100) + 130, (y * 100) + 70);
    TicTacToe.context.strokeStyle = "white";
    TicTacToe.context.lineWidth = 3;
    TicTacToe.context.stroke();
}

function drawO(x, y) {
    "strict mode";
    TicTacToe.context.beginPath();
    TicTacToe.context.arc((x * 100) + 100, (y * 100) + 100, 30, 0, 2 * Math.PI, false);
    TicTacToe.context.strokeStyle = "white";
    TicTacToe.context.lineWidth = 3;
    TicTacToe.context.stroke();
}

function drawWinLine(x, y, player) {
    "strict mode";
    if (checkHorizontal(y, player)) {
        TicTacToe.context.beginPath();
        TicTacToe.context.moveTo(50, (y * 100) + 100);
        TicTacToe.context.lineTo(350, (y * 100) + 100);
        TicTacToe.context.strokeStyle = "#7519FF";
        TicTacToe.context.lineWidth = 1;
        TicTacToe.context.stroke();
    } else if (checkVertical(x, player)) {
        TicTacToe.context.beginPath();
        TicTacToe.context.moveTo((x * 100) + 100, 50);
        TicTacToe.context.lineTo((x * 100) + 100, 350);
        TicTacToe.context.strokeStyle = "#7519FF";
        TicTacToe.context.lineWidth = 1;
        TicTacToe.context.stroke();
    } else if (checkDiagonalRight(player)) {
        TicTacToe.context.beginPath();
        TicTacToe.context.moveTo(50, 50);
        TicTacToe.context.lineTo(350, 350);
        TicTacToe.context.strokeStyle = "#7519FF";
        TicTacToe.context.lineWidth = 1;
        TicTacToe.context.stroke();
    } else if (checkDiagonalLeft(player)) {
        TicTacToe.context.beginPath();
        TicTacToe.context.moveTo(50, 350);
        TicTacToe.context.lineTo(350, 50);
        TicTacToe.context.strokeStyle = "#7519FF";
        TicTacToe.context.lineWidth = 1;
        TicTacToe.context.stroke();
    }
}

function checkForWin(x, y, player) {
    "strict mode";
    if (!(checkHorizontal(y, player) || checkVertical(x, player))) {
        if (x === 0 && y === 0) {
            return checkDiagonalRight(player);
        } else if (x === 2 && y === 0) {
            return checkDiagonalLeft(player);
        } else if (x === 1 && y === 1) {
            return checkDiagonalRight(player) ||
                    checkDiagonalLeft(player);
        } else if (x === 0 && y === 2) {
            return checkDiagonalLeft(player);
        } else if (x === 2 && y === 2) {
            return checkDiagonalRight(player);
        } else {
            return false;
        }
    }
    
    return true;
}

function checkHorizontal(y, player) {
    "strict mode";
    return TicTacToe.board[0][y] === player && 
            TicTacToe.board[1][y] === player && 
            TicTacToe.board[2][y] === player;
}

function checkVertical(x, player) {
    "strict mode";
    return TicTacToe.board[x][0] === player && 
            TicTacToe.board[x][1] === player && 
            TicTacToe.board[x][2] === player;
}

function checkDiagonalRight(player) {
    "strict mode";
    return TicTacToe.board[0][0] === player && 
            TicTacToe.board[1][1] === player && 
            TicTacToe.board[2][2] === player;
}

function checkDiagonalLeft(player) {
    "strict mode";
    return TicTacToe.board[2][0] === player && 
            TicTacToe.board[1][1] === player && 
            TicTacToe.board[0][2] === player;
}
